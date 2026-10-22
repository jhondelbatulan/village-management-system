<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\Resident;
use App\Models\Village;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function households(): View
    {
        $user = auth()->user();
        $role = $user->role?->role_slug;

        // Stage 1 (Admin): pending submissions from their village.
        // Stage 2 (Super Admin): admin-approved records awaiting final decision.
        $query = Household::with(['village', 'submittedBy', 'adminApprovedBy', 'approvedBy']);

        if ($role === 'admin' || $role === 'secretary') {
            $query->where('village_id', $user->village_id);
            $query->whereIn('approval_status', ['pending']);
        } elseif ($role === 'super_admin') {
            $query->whereIn('approval_status', ['admin_approved', 'approved', 'rejected']);
        }

        if (request('status')) {
            $query->where('approval_status', request('status'));
        }

        if (request('village_id') && $role === 'super_admin') {
            $query->where('village_id', request('village_id'));
        }

        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('household_number', 'like', "%{$search}%")
                    ->orWhere('block_number', 'like', "%{$search}%")
                    ->orWhere('lot_number', 'like', "%{$search}%")
                    ->orWhere('household_head_name', 'like', "%{$search}%");
            });
        }

        $households = $query->latest()->paginate(15)->withQueryString();
        $villages = Village::where('status', 'active')->orderBy('village_name')->get();

        $pendingCount = Household::where('approval_status', 'pending')
            ->when($role === 'admin', fn ($q) => $q->where('village_id', $user->village_id))
            ->count();

        $adminApprovedCount = Household::where('approval_status', 'admin_approved')
            ->when($role === 'admin', fn ($q) => $q->where('village_id', $user->village_id))
            ->count();

        $approvedCount = Household::where('approval_status', 'approved')
            ->when($role === 'admin', fn ($q) => $q->where('village_id', $user->village_id))
            ->count();

        $rejectedCount = Household::where('approval_status', 'rejected')
            ->when($role === 'admin', fn ($q) => $q->where('village_id', $user->village_id))
            ->count();

        $counts = compact('pendingCount', 'adminApprovedCount', 'approvedCount', 'rejectedCount');

        return view('approvals.households', compact('households', 'villages', 'counts', 'role'));
    }

    public function approveHousehold(Household $household): RedirectResponse
    {
        return DB::transaction(function () use ($household): RedirectResponse {
            // Lock the row so only one approval action can be processed at a time.
            $household = Household::query()->lockForUpdate()->findOrFail($household->id);

            $user = auth()->user();
            $role = $user->role?->role_slug;

            // Stage 1 — Admin (President) reviews pending submissions from their village.
            if ($role === 'admin') {
                if ($household->village_id !== $user->village_id) {
                    abort(403, 'You can only approve households in your village.');
                }

                if ($household->approval_status !== 'pending') {
                    return back()->with('error', 'This household request has already been processed.');
                }

                $household->update([
                    'approval_status' => 'admin_approved',
                    'admin_approved_by' => auth()->id(),
                    'admin_approved_at' => now(),
                ]);

                AuditLogService::log('Admin Approve Household', 'Approvals', "Admin approved household: {$household->household_number} (pending Super Admin final approval)");
                app(NotificationService::class)->notifySuperAdmins('Approval Update', "Household approved by Admin ({$household->notification_label}). Pending your final approval.", route('approvals.households'));
                app(NotificationService::class)->notifyUser($household->submitted_by, 'approval', 'Household Update', "Household approved by Admin ({$household->notification_label}). Awaiting Super Admin final approval.", route('households.show', $household));

                return redirect()->route('approvals.households')->with('success', "Household {$household->household_number} approved. Awaiting Super Admin final approval.");
            }

            // Stage 2 — Super Admin gives the final approval of admin-approved records only.
            if ($role === 'super_admin') {
                if ($household->approval_status !== 'admin_approved') {
                    return back()->with('error', $household->approval_status === 'pending'
                        ? 'Only admin-approved households can be approved at this stage.'
                        : 'This household request has already been processed.');
                }

                $household->update([
                    'approval_status' => 'approved',
                    'status' => 'active',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

                AuditLogService::log('Final Approve Household', 'Approvals', "Super Admin approved household: {$household->household_number}");
                app(NotificationService::class)->notifyUser($household->submitted_by, 'approval', 'Household Update', "Household has been approved by Super Admin ({$household->notification_label}).", route('households.show', $household));

                return redirect()->route('approvals.households')->with('success', "Household {$household->household_number} approved successfully.");
            }

            return back()->with('error', 'You cannot approve this household at this stage.');
        });
    }

    public function rejectHousehold(Household $household): RedirectResponse
    {
        return DB::transaction(function () use ($household): RedirectResponse {
            // Lock the row so only one approval action can be processed at a time.
            $household = Household::query()->lockForUpdate()->findOrFail($household->id);

            $user = auth()->user();
            $role = $user->role?->role_slug;

            if (! in_array($role, ['admin', 'super_admin'])) {
                abort(403);
            }

            // Admin rejects pending submissions in their village (Stage 1).
            if ($role === 'admin') {
                if ($household->village_id !== $user->village_id) {
                    abort(403, 'You can only reject households in your village.');
                }

                if ($household->approval_status !== 'pending') {
                    return back()->with('error', 'This household request has already been processed.');
                }
            }

            // Super Admin rejects admin-approved records (Stage 2).
            if ($role === 'super_admin' && $household->approval_status !== 'admin_approved') {
                return back()->with('error', $household->approval_status === 'pending'
                    ? 'Only admin-approved households can be rejected at this stage.'
                    : 'This household request has already been processed.');
            }

            $remarks = request('remarks');
            if (empty($remarks)) {
                return back()->with('error', 'Remarks are required when rejecting.');
            }

            $household->update([
                'approval_status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'remarks' => $remarks,
            ]);

            AuditLogService::log('Reject Household', 'Approvals', "Rejected household: {$household->household_number}. Reason: {$remarks}");
            app(NotificationService::class)->notifyUser($household->submitted_by, 'approval', 'Household Update', "Household has been rejected ({$household->notification_label}). Reason: {$remarks}", route('households.show', $household));
            if ($role === 'super_admin') {
                app(NotificationService::class)->notifyAdmins($household->village_id, 'Approval Required', "Household was rejected by Super Admin ({$household->notification_label}). Reason: {$remarks}", route('approvals.households'), 'warning');
            }

            return redirect()->route('approvals.households')->with('success', "Household {$household->household_number} has been rejected.");
        });
    }

    public function residents(): View
    {
        $user = auth()->user();
        $role = $user->role?->role_slug;

        $query = Resident::with(['village', 'household', 'submittedBy', 'adminApprovedBy', 'approvedBy']);

        if ($role === 'admin' || $role === 'secretary') {
            $query->where('village_id', $user->village_id);
            $query->whereIn('approval_status', ['pending']);
        } elseif ($role === 'super_admin') {
            $query->whereIn('approval_status', ['admin_approved', 'approved', 'rejected']);
        }

        if (request('status')) {
            $query->where('approval_status', request('status'));
        }

        if (request('village_id') && $role === 'super_admin') {
            $query->where('village_id', request('village_id'));
        }

        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('resident_code', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $residents = $query->latest()->paginate(15)->withQueryString();
        $villages = Village::where('status', 'active')->orderBy('village_name')->get();

        $pendingCount = Resident::where('approval_status', 'pending')
            ->when($role === 'admin', fn ($q) => $q->where('village_id', $user->village_id))
            ->count();

        $adminApprovedCount = Resident::where('approval_status', 'admin_approved')
            ->when($role === 'admin', fn ($q) => $q->where('village_id', $user->village_id))
            ->count();

        $approvedCount = Resident::where('approval_status', 'approved')
            ->when($role === 'admin', fn ($q) => $q->where('village_id', $user->village_id))
            ->count();

        $rejectedCount = Resident::where('approval_status', 'rejected')
            ->when($role === 'admin', fn ($q) => $q->where('village_id', $user->village_id))
            ->count();

        $counts = compact('pendingCount', 'adminApprovedCount', 'approvedCount', 'rejectedCount');

        return view('approvals.residents', compact('residents', 'villages', 'counts', 'role'));
    }

    public function approveResident(Resident $resident): RedirectResponse
    {
        return DB::transaction(function () use ($resident): RedirectResponse {
            // Lock the row so only one approval action can be processed at a time.
            $resident = Resident::query()->lockForUpdate()->findOrFail($resident->id);

            $user = auth()->user();
            $role = $user->role?->role_slug;

            // Stage 1 — Admin (President) reviews pending submissions from their village.
            if ($role === 'admin') {
                if ($resident->village_id !== $user->village_id) {
                    abort(403, 'You can only approve residents in your village.');
                }

                if ($resident->approval_status !== 'pending') {
                    return back()->with('error', 'This resident request has already been processed.');
                }

                $resident->update([
                    'approval_status' => 'admin_approved',
                    'admin_approved_by' => auth()->id(),
                    'admin_approved_at' => now(),
                ]);

                AuditLogService::log('Admin Approve Resident', 'Approvals', "Admin approved resident: {$resident->full_name} (pending Super Admin final approval)");
                app(NotificationService::class)->notifySuperAdmins('Approval Update', "Resident {$resident->full_name} approved by Admin. Pending your final approval.", route('approvals.residents'));
                app(NotificationService::class)->notifyUser($resident->submitted_by, 'approval', 'Resident Update', "Resident {$resident->full_name} approved by Admin. Awaiting Super Admin final approval.", route('residents.show', $resident));

                return redirect()->route('approvals.residents')->with('success', "Resident {$resident->full_name} approved. Awaiting Super Admin final approval.");
            }

            // Stage 2 — Super Admin gives the final approval of admin-approved records only.
            if ($role === 'super_admin') {
                if ($resident->approval_status !== 'admin_approved') {
                    return back()->with('error', $resident->approval_status === 'pending'
                        ? 'Only admin-approved residents can be approved at this stage.'
                        : 'This resident request has already been processed.');
                }

                $resident->update([
                    'approval_status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

                AuditLogService::log('Final Approve Resident', 'Approvals', "Super Admin approved resident: {$resident->full_name}");
                app(NotificationService::class)->notifyUser($resident->submitted_by, 'approval', 'Resident Update', "Resident {$resident->full_name} has been approved by Super Admin.", route('residents.show', $resident));

                return redirect()->route('approvals.residents')->with('success', "Resident {$resident->full_name} approved successfully.");
            }

            return back()->with('error', 'You cannot approve this resident at this stage.');
        });
    }

    public function rejectResident(Resident $resident): RedirectResponse
    {
        return DB::transaction(function () use ($resident): RedirectResponse {
            // Lock the row so only one approval action can be processed at a time.
            $resident = Resident::query()->lockForUpdate()->findOrFail($resident->id);

            $user = auth()->user();
            $role = $user->role?->role_slug;

            if (! in_array($role, ['admin', 'super_admin'])) {
                abort(403);
            }

            // Admin rejects pending submissions in their village (Stage 1).
            if ($role === 'admin') {
                if ($resident->village_id !== $user->village_id) {
                    abort(403, 'You can only reject residents in your village.');
                }

                if ($resident->approval_status !== 'pending') {
                    return back()->with('error', 'This resident request has already been processed.');
                }
            }

            // Super Admin rejects admin-approved records (Stage 2).
            if ($role === 'super_admin' && $resident->approval_status !== 'admin_approved') {
                return back()->with('error', $resident->approval_status === 'pending'
                    ? 'Only admin-approved residents can be rejected at this stage.'
                    : 'This resident request has already been processed.');
            }

            $remarks = request('remarks');
            if (empty($remarks)) {
                return back()->with('error', 'Remarks are required when rejecting.');
            }

            $resident->update([
                'approval_status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'remarks' => $remarks,
            ]);

            AuditLogService::log('Reject Resident', 'Approvals', "Rejected resident: {$resident->full_name}. Reason: {$remarks}");
            app(NotificationService::class)->notifyUser($resident->submitted_by, 'approval', 'Resident Update', "Resident {$resident->full_name} has been rejected. Reason: {$remarks}", route('residents.show', $resident));
            if ($role === 'super_admin') {
                app(NotificationService::class)->notifyAdmins($resident->village_id, 'Approval Required', "Resident {$resident->full_name} was rejected by Super Admin. Reason: {$remarks}", route('approvals.residents'), 'warning');
            }

            return redirect()->route('approvals.residents')->with('success', "Resident {$resident->full_name} has been rejected.");
        });
    }
}
