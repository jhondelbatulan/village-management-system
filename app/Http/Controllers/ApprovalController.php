<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\Resident;
use App\Models\User;
use App\Models\Village;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function households(): View
    {
        $user = auth()->user();
        $role = $user->role?->role_slug;

        $query = Household::with(['village', 'submittedBy', 'adminApprovedBy', 'approvedBy']);

        if (in_array($role, ['admin', 'secretary'])) {
            $query->where('village_id', $user->village_id);
            $query->whereIn('approval_status', ['pending']);
        } elseif ($role === 'super_admin') {
            $query->whereIn('approval_status', ['pending', 'admin_approved', 'approved', 'rejected']);
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
            ->when($role === 'admin', fn($q) => $q->where('village_id', $user->village_id))
            ->count();

        $adminApprovedCount = Household::where('approval_status', 'admin_approved')
            ->when($role === 'admin', fn($q) => $q->where('village_id', $user->village_id))
            ->count();

        $approvedCount = Household::where('approval_status', 'approved')
            ->when($role === 'admin', fn($q) => $q->where('village_id', $user->village_id))
            ->count();

        $rejectedCount = Household::where('approval_status', 'rejected')
            ->when($role === 'admin', fn($q) => $q->where('village_id', $user->village_id))
            ->count();

        $counts = compact('pendingCount', 'adminApprovedCount', 'approvedCount', 'rejectedCount');

        return view('approvals.households', compact('households', 'villages', 'counts', 'role'));
    }

    public function approveHousehold(Household $household): RedirectResponse
    {
        $user = auth()->user();
        $role = $user->role?->role_slug;

        if (in_array($role, ['admin', 'secretary'])) {
            if ($household->village_id !== $user->village_id) {
                abort(403, 'You can only approve households in your village.');
            }

            $household->update([
                'approval_status' => 'admin_approved',
                'admin_approved_by' => auth()->id(),
                'admin_approved_at' => now(),
            ]);

            AuditLogService::log('Admin Approve Household', 'Approvals', "Admin approved household: {$household->household_number}");
            $this->createNotification("Household {$household->household_number} approved by Admin. Pending Super Admin approval.", 'household_approved');

            return redirect()->route('approvals.households')->with('success', "Household {$household->household_number} approved. Awaiting Super Admin final approval.");
        }

        if ($role === 'super_admin' && in_array($household->approval_status, ['pending', 'admin_approved'])) {
            $household->update([
                'approval_status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            AuditLogService::log('Final Approve Household', 'Approvals', "Super Admin approved household: {$household->household_number}");
            $this->createNotification("Household {$household->household_number} finally approved by Super Admin.", 'household_approved', $household->submittedBy);

            return redirect()->route('approvals.households')->with('success', "Household {$household->household_number} approved successfully.");
        }

        return back()->with('error', 'You cannot approve this household at this stage.');
    }

    public function approveResidentBySecretary(Resident $resident): RedirectResponse
    {
        $user = auth()->user();

        if ($resident->village_id !== $user->village_id) {
            abort(403, 'You can only approve residents in your village.');
        }

        if ($resident->approval_status !== 'pending') {
            return back()->with('error', 'This resident cannot be approved at this stage.');
        }

        $resident->update([
            'approval_status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        AuditLogService::log('Secretary Approve Resident', 'Approvals', "Secretary approved resident: {$resident->full_name}");
        $this->createNotification("Resident {$resident->full_name} has been approved by Secretary.", 'resident_approved', $resident->submittedBy);

        return redirect()->route('approvals.residents')->with('success', "Resident {$resident->full_name} approved successfully.");
    }

    public function rejectResidentBySecretary(Resident $resident): RedirectResponse
    {
        $user = auth()->user();

        if ($resident->village_id !== $user->village_id) {
            abort(403, 'You can only reject residents in your village.');
        }

        if ($resident->approval_status !== 'pending') {
            return back()->with('error', 'This resident cannot be rejected at this stage.');
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

        AuditLogService::log('Secretary Reject Resident', 'Approvals', "Secretary rejected resident: {$resident->full_name}. Reason: {$remarks}");
        $this->createNotification("Resident {$resident->full_name} rejected. Reason: {$remarks}", 'resident_rejected', $resident->submittedBy);

        return redirect()->route('approvals.residents')->with('success', "Resident {$resident->full_name} has been rejected.");
    }

    public function rejectHousehold(Household $household): RedirectResponse
    {
        $user = auth()->user();
        $role = $user->role?->role_slug;

        if (!in_array($role, ['admin', 'super_admin', 'secretary'])) {
            abort(403);
        }

        if ($role === 'admin' && $household->village_id !== $user->village_id) {
            abort(403, 'You can only reject households in your village.');
        }

        if (!in_array($household->approval_status, ['pending', 'admin_approved'])) {
            return back()->with('error', 'This household cannot be rejected at this stage.');
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
        $this->createNotification("Household {$household->household_number} rejected. Reason: {$remarks}", 'household_rejected', $household->submittedBy);

        return redirect()->route('approvals.households')->with('success', "Household {$household->household_number} has been rejected.");
    }

    public function residents(): View
    {
        $user = auth()->user();
        $role = $user->role?->role_slug;

        $query = Resident::with(['village', 'household', 'submittedBy', 'adminApprovedBy', 'approvedBy']);

        if (in_array($role, ['admin', 'secretary'])) {
            $query->where('village_id', $user->village_id);
            $query->whereIn('approval_status', ['pending']);
        } elseif ($role === 'super_admin') {
            $query->whereIn('approval_status', ['pending', 'admin_approved', 'approved', 'rejected']);
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
            ->when($role === 'admin', fn($q) => $q->where('village_id', $user->village_id))
            ->count();

        $adminApprovedCount = Resident::where('approval_status', 'admin_approved')
            ->when($role === 'admin', fn($q) => $q->where('village_id', $user->village_id))
            ->count();

        $approvedCount = Resident::where('approval_status', 'approved')
            ->when($role === 'admin', fn($q) => $q->where('village_id', $user->village_id))
            ->count();

        $rejectedCount = Resident::where('approval_status', 'rejected')
            ->when($role === 'admin', fn($q) => $q->where('village_id', $user->village_id))
            ->count();

        $counts = compact('pendingCount', 'adminApprovedCount', 'approvedCount', 'rejectedCount');

        return view('approvals.residents', compact('residents', 'villages', 'counts', 'role'));
    }

    public function approveResident(Resident $resident): RedirectResponse
    {
        $user = auth()->user();
        $role = $user->role?->role_slug;

        if ($role === 'admin') {
            if ($resident->village_id !== $user->village_id) {
                abort(403, 'You can only approve residents in your village.');
            }

            $resident->update([
                'approval_status' => 'admin_approved',
                'admin_approved_by' => auth()->id(),
                'admin_approved_at' => now(),
            ]);

            AuditLogService::log('Admin Approve Resident', 'Approvals', "Admin approved resident: {$resident->full_name}");
            $this->createNotification("Resident {$resident->full_name} approved by Admin. Pending Super Admin approval.", 'resident_approved');

            return redirect()->route('approvals.residents')->with('success', "Resident {$resident->full_name} approved. Awaiting Super Admin final approval.");
        }

        if ($role === 'super_admin' && in_array($resident->approval_status, ['pending', 'admin_approved'])) {
            $resident->update([
                'approval_status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            AuditLogService::log('Final Approve Resident', 'Approvals', "Super Admin approved resident: {$resident->full_name}");
            $this->createNotification("Resident {$resident->full_name} finally approved by Super Admin.", 'resident_approved', $resident->submittedBy);

            return redirect()->route('approvals.residents')->with('success', "Resident {$resident->full_name} approved successfully.");
        }

        return back()->with('error', 'You cannot approve this resident at this stage.');
    }

    public function rejectResident(Resident $resident): RedirectResponse
    {
        $user = auth()->user();
        $role = $user->role?->role_slug;

        if (!in_array($role, ['admin', 'super_admin'])) {
            abort(403);
        }

        if ($role === 'admin' && $resident->village_id !== $user->village_id) {
            abort(403, 'You can only reject residents in your village.');
        }

        if (!in_array($resident->approval_status, ['pending', 'admin_approved'])) {
            return back()->with('error', 'This resident cannot be rejected at this stage.');
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
        $this->createNotification("Resident {$resident->full_name} rejected. Reason: {$remarks}", 'resident_rejected', $resident->submittedBy);

        return redirect()->route('approvals.residents')->with('success', "Resident {$resident->full_name} has been rejected.");
    }

    private function createNotification(string $message, string $type, ?User $targetUser = null): void
    {
        try {
            $service = app(NotificationService::class);
            $titles = [
                'household_approved' => 'Household Approved',
                'household_rejected' => 'Household Rejected',
                'resident_approved' => 'Resident Approved',
                'resident_rejected' => 'Resident Rejected',
            ];
            $icons = [
                'household_approved' => 'bi-house-check',
                'household_rejected' => 'bi-house-x',
                'resident_approved' => 'bi-person-check',
                'resident_rejected' => 'bi-person-x',
            ];
            $colors = [
                'household_approved' => 'success',
                'household_rejected' => 'danger',
                'resident_approved' => 'success',
                'resident_rejected' => 'danger',
            ];

            // Notify specific user if provided, otherwise notify the relevant role(s)
            if ($targetUser) {
                $service->create($targetUser, $type, $titles[$type] ?? 'Approval Update', $message, $icons[$type] ?? 'bi-bell', $colors[$type] ?? 'secondary');
            } else {
                // Notify super admins by default
                $superAdmins = User::whereHas('role', fn($q) => $q->where('role_slug', 'super_admin'))->where('status', 'active')->get();
                foreach ($superAdmins as $sa) {
                    $service->create($sa, $type, $titles[$type] ?? 'Approval Update', $message, $icons[$type] ?? 'bi-bell', $colors[$type] ?? 'secondary');
                }
            }
        } catch (\Throwable $e) {
            // Silently fail - notifications should never break the app
        }
    }
}
