<?php

namespace App\Http\Controllers;

use App\Http\Requests\HouseholdRequest\ApproveHouseholdRequest;
use App\Http\Requests\HouseholdRequest\RequestMemberRequest;
use App\Http\Requests\HouseholdRequest\StoreHouseholdRequest;
use App\Http\Requests\HouseholdRequest\UpdateHouseholdRequest;
use App\Models\Household;
use App\Models\Resident;
use App\Models\User;
use App\Models\Village;
use App\Services\AuditLogService;
use App\Services\GisService;
use App\Services\NotificationService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HouseholdController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $query = Household::with('village', 'householdHead', 'submittedBy', 'approvedBy', 'adminApprovedBy');

        if (in_array($user->role?->role_slug, ['admin', 'secretary', 'treasurer'])) {
            $query->where('village_id', $user->village_id);
        }

        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('household_number', 'like', "%{$search}%")
                    ->orWhere('block_number', 'like', "%{$search}%")
                    ->orWhere('lot_number', 'like', "%{$search}%")
                    ->orWhere('household_head_name', 'like', "%{$search}%")
                    ->orWhere('purok', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if (request('village_id')) {
            $query->where('village_id', request('village_id'));
        }

        if (request('status')) {
            $query->where('status', request('status'));
        }

        if (request('approval_status')) {
            $query->where('approval_status', request('approval_status'));
        }

        if (request('block_number')) {
            $query->where('block_number', request('block_number'));
        }

        if (request('lot_number')) {
            $query->where('lot_number', request('lot_number'));
        }

        if (request('sort') === 'az') {
            $query->orderBy('household_head_name');
        } elseif (request('sort') === 'za') {
            $query->orderByDesc('household_head_name');
        } elseif (request('sort') === 'newest') {
            $query->latest();
        } else {
            $query->orderByRaw('CAST(block_number AS UNSIGNED)')
                ->orderByRaw('CAST(lot_number AS UNSIGNED)');
        }

        $households = $query->paginate(15)->withQueryString();
        $archivedHouseholds = Household::onlyTrashed()->latest()->paginate(10);
        $villages = Village::where('status', 'active')->orderBy('village_name')->get();

        $blockNumbers = Household::where('block_number', '!=', null)
            ->where('block_number', '!=', '')
            ->distinct()
            ->pluck('block_number')
            ->sort(fn ($a, $b) => strnatcmp((string) $a, (string) $b))
            ->values();

        $lotNumbers = Household::where('lot_number', '!=', null)
            ->where('lot_number', '!=', '')
            ->distinct()
            ->pluck('lot_number')
            ->sort(fn ($a, $b) => strnatcmp((string) $a, (string) $b))
            ->values();

        return view('households.index', compact('households', 'archivedHouseholds', 'villages', 'blockNumbers', 'lotNumbers'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $villages = Village::where('status', 'active')->orderBy('village_name')->get();

        if (in_array($user->role?->role_slug, ['admin', 'secretary', 'treasurer'])) {
            $villages = $villages->where('id', $user->village_id);
        }

        $latestHouseholds = app(GisService::class)->getLatestMappedHouseholds(null, 25);

        return view('households.create', compact('villages', 'latestHouseholds'));
    }

    public function store(StoreHouseholdRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['household_number'] = Household::generateHouseholdNumber($data['village_id']);
        $data['registration_date'] = $data['registration_date'] ?? now()->toDateString();
        $data['approval_status'] = 'draft';
        $data['submitted_by'] = null;

        $household = Household::create($data);

        $village = Village::find($data['village_id']);
        if ($village) {
            $village->recalculateCounts();
        }

        AuditLogService::log('Create Household', 'Household Management', "Created household: {$household->household_number}");

        return redirect()->route('households.index')->with('success', "Household {$household->household_number} created successfully. Please submit it for approval.");
    }

    public function show(Household $household): View
    {
        $household->load(['village', 'householdHead', 'residents', 'submittedBy', 'approvedBy', 'adminApprovedBy']);

        return view('households.show', compact('household'));
    }

    public function myHousehold(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user->household_id) {
            return back()->with('error', 'You are not assigned to any household.');
        }

        $household = Household::with(['village', 'householdHead', 'residents', 'submittedBy', 'approvedBy', 'adminApprovedBy', 'billings.paymentType', 'payments'])
            ->find($user->household_id);

        if (! $household) {
            return back()->with('error', 'Your household record no longer exists. Please contact an administrator.');
        }

        $paymentService = app(PaymentService::class);
        $balance = $paymentService->getHouseholdBalance($household->id);

        $billings = $paymentService->getHouseholdBillings($household->id);
        $payments = $paymentService->getHouseholdPayments($household->id);

        $pendingMembers = Resident::where('household_id', $household->id)
            ->whereIn('approval_status', ['pending', 'admin_approved'])
            ->latest()
            ->get();

        return view('households.my-household', compact('household', 'balance', 'billings', 'payments', 'pendingMembers'));
    }

    public function edit(Household $household): View
    {
        $villages = Village::where('status', 'active')->orderBy('village_name')->get();

        $latestHouseholds = app(GisService::class)->getLatestMappedHouseholds($household->village_id, 25);

        return view('households.edit', compact('household', 'villages', 'latestHouseholds'));
    }

    public function update(UpdateHouseholdRequest $request, Household $household): RedirectResponse
    {
        $data = $request->validated();

        if ($household->approval_status === 'rejected') {
            $data['approval_status'] = 'draft';
            $data['submitted_by'] = null;
            $data['approved_by'] = null;
            $data['approved_at'] = null;
            $data['admin_approved_by'] = null;
            $data['admin_approved_at'] = null;
            $data['remarks'] = null;
        }

        $household->update($data);

        $village = Village::find($household->village_id);
        if ($village) {
            $village->recalculateCounts();
        }

        AuditLogService::log('Update Household', 'Household Management', "Updated household: {$household->household_number}");

        return redirect()->route('households.index')->with('success', 'Household updated successfully.');
    }

    public function destroy(Household $household): RedirectResponse
    {
        $number = $household->household_number;

        DB::transaction(function () use ($household): void {
            $household->delete();

            // Archive every resident of this household (any approval status),
            // stamped with the household's deleted_at so restore can find them.
            Resident::withTrashed()
                ->where('household_id', $household->id)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => $household->deleted_at]);
        });

        $village = Village::find($household->village_id);
        if ($village) {
            $village->recalculateCounts();
        }

        AuditLogService::log('Archive Household', 'Household Management', "Archived household: {$number} and its residents.");

        app(NotificationService::class)->notifyHouseholdResidents(
            [$household->id],
            'warning',
            'Household Archived',
            "Your household ({$number}) has been archived by the administration.",
            route('households.my-household')
        );

        app(NotificationService::class)->notifyAdmins(
            $household->village_id,
            'Household Archived',
            "Household \"{$number}\" and its residents have been archived.",
            route('households.index'),
            'warning'
        );

        return redirect()->route('households.index')->with('success', "Household \"{$number}\" and its residents have been archived.");
    }

    public function restore(int $id): RedirectResponse
    {
        $household = Household::withTrashed()->findOrFail($id);
        $archivedAt = $household->deleted_at;

        DB::transaction(function () use ($household, $archivedAt): void {
            $household->restore();

            // Restore only the residents archived together with this household;
            // residents archived earlier on their own stay archived.
            Resident::withTrashed()
                ->where('household_id', $household->id)
                ->where('deleted_at', $archivedAt)
                ->restore();
        });

        $village = Village::find($household->village_id);
        if ($village) {
            $village->recalculateCounts();
        }

        AuditLogService::log('Restore Household', 'Household Management', "Restored household: {$household->household_number} and its residents.");

        return redirect()->route('households.index')->with('success', "Household \"{$household->household_number}\" and its residents have been restored.");
    }

    public function forceDelete(Request $request, int $id): RedirectResponse
    {
        $household = Household::withTrashed()->findOrFail($id);
        $number = $household->household_number;

        // Capture linked users before force-delete nulls their household_id.
        $householdUserIds = User::where('household_id', $household->id)->pluck('id');

        try {
            DB::transaction(function () use ($household): void {
                $household->participations()->delete();

                Resident::withTrashed()
                    ->where('household_id', $household->id)
                    ->each(function (Resident $resident): void {
                        $resident->participations()->delete();
                        $resident->forceDelete();
                    });

                $household->forceDelete();
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', "Household \"{$number}\" could not be deleted because related records still reference it.");
        }

        $village = Village::find($household->village_id);
        if ($village) {
            $village->recalculateCounts();
        }

        AuditLogService::log('Permanently Delete Household', 'Household Management', "Permanently deleted household: {$number} and its residents.");

        $notifications = app(NotificationService::class);
        foreach ($householdUserIds as $userId) {
            $notifications->notifyUser(
                $userId,
                'warning',
                'Household Removed',
                "Your household ({$number}) has been permanently deleted by the administration."
            );
        }

        return redirect()->route('households.index')->with('success', "Household \"{$number}\" and its residents have been permanently deleted.");
    }

    public function submit(Household $household): RedirectResponse
    {
        if (! in_array($household->approval_status, ['draft', 'rejected'])) {
            return back()->with('error', 'Only draft or rejected records can be submitted for approval.');
        }

        $data = [
            'approval_status' => 'pending',
            'submitted_by' => auth()->id(),
        ];

        if ($household->approval_status === 'rejected') {
            $data['approved_by'] = null;
            $data['approved_at'] = null;
            $data['admin_approved_by'] = null;
            $data['admin_approved_at'] = null;
            $data['remarks'] = null;
        }

        $household->update($data);

        AuditLogService::log('Submit Household', 'Household Management', "Submitted household for approval: {$household->household_number}");

        $this->notifyAdmins("New household submitted for approval ({$household->notification_label}).", 'approval', $household->village_id);

        return redirect()->route('households.show', $household)->with('success', 'Household submitted for approval successfully.');
    }

    public function requestMemberForm(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user->household_id) {
            return redirect()->route('households.my-household')->with('error', 'You are not assigned to any household.');
        }

        return view('households.request-member');
    }

    public function requestMember(RequestMemberRequest $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->household_id) {
            return back()->with('error', 'You are not assigned to any household.');
        }

        $validated = $request->validated();

        $household = Household::findOrFail($user->household_id);

        $data = array_merge($validated, [
            'resident_code' => Resident::generateResidentCode(),
            'household_id' => $household->id,
            'village_id' => $household->village_id,
            'nationality' => $validated['nationality'] ?? 'Filipino',
            'approval_status' => 'pending',
            'submitted_by' => $user->id,
            'status' => 'active',
        ]);

        $resident = Resident::create($data);

        $this->notifyAdmins("New resident {$resident->full_name} is added on the household ({$household->notification_label}).", 'approval', $household->village_id);

        AuditLogService::log('Request Add Member', 'Household Management', "Resident requested to add {$resident->full_name} to household {$household->household_number}");

        return redirect()->route('households.my-household')->with('success', 'Your request to add a new member has been submitted for admin approval.');
    }

    public function toggleStatus(Household $household): RedirectResponse
    {
        $user = auth()->user();
        $role = $user->role?->role_slug;

        if (in_array($role, ['admin', 'secretary']) && $household->village_id !== $user->village_id) {
            abort(403, 'You can only manage households in your village.');
        }

        $newStatus = $household->status === 'active' ? 'inactive' : 'active';
        $household->update(['status' => $newStatus]);

        if ($newStatus === 'inactive') {
            User::where('household_id', $household->id)->where('status', 'active')->update(['status' => 'inactive']);
        } else {
            User::where('household_id', $household->id)->where('status', 'inactive')->update(['status' => 'active']);
        }

        $village = Village::find($household->village_id);
        if ($village) {
            $village->recalculateCounts();
        }

        AuditLogService::log('Toggle Household Status', 'Household Management', "Toggled household {$household->household_number} to {$newStatus}");

        return redirect()->back()->with('success', "Household status changed to {$newStatus}.".($newStatus === 'inactive' ? ' All linked user accounts have been deactivated.' : ''));
    }

    public function approve(Household $household, ApproveHouseholdRequest $request): RedirectResponse
    {
        $data = $request->validated();

        return DB::transaction(function () use ($household, $data): RedirectResponse {
            // Lock the row so only one approval action can be processed at a time.
            $household = Household::query()->lockForUpdate()->findOrFail($household->id);

            $action = $data['approval_status'];
            $userRole = auth()->user()->role?->role_slug;

            if ($action === 'approved') {
                // Stage 1 — Admin reviews pending submissions from their village.
                if ($userRole === 'admin' && $household->approval_status === 'pending') {
                    $household->update([
                        'approval_status' => 'admin_approved',
                        'admin_approved_by' => auth()->id(),
                        'admin_approved_at' => now(),
                        'remarks' => $data['remarks'] ?? null,
                    ]);

                    AuditLogService::log('Admin Approve Household', 'Household Management', "Admin approved household: {$household->household_number} (pending super admin final approval)");

                    $this->notifySuperAdmins("Household approved by Admin ({$household->notification_label}). Pending your final approval.");
                    $this->notifySubmitter($household, "Household approved by Admin ({$household->notification_label}). Awaiting Super Admin final approval.");

                    return redirect()->route('households.show', $household)->with('success', 'Household approved by Admin. Awaiting Super Admin final approval.');
                }

                // Stage 2 — Super Admin gives the final approval of admin-approved records only.
                if ($userRole === 'super_admin' && $household->approval_status === 'admin_approved') {
                    $household->update([
                        'approval_status' => 'approved',
                        'status' => 'active',
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                        'remarks' => $data['remarks'] ?? null,
                    ]);

                    AuditLogService::log('Final Approve Household', 'Household Management', "Super Admin final approved household: {$household->household_number}");

                    $this->notifySubmitter($household, "Household has been approved by Super Admin ({$household->notification_label}).");

                    return redirect()->route('households.show', $household)->with('success', 'Household has been approved successfully.');
                }

                return back()->with('error', $this->alreadyProcessed($household, $userRole)
                    ? 'This household request has already been processed.'
                    : 'You cannot approve this household at this stage.');
            }

            if ($action === 'rejected') {
                // Admin rejects pending submissions in their village (Stage 1).
                if ($userRole === 'admin' && $household->approval_status === 'pending') {
                    $household->update([
                        'approval_status' => 'rejected',
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                        'remarks' => $data['remarks'] ?? null,
                    ]);

                    AuditLogService::log('Reject Household', 'Household Management', "Rejected household: {$household->household_number}");

                    $this->notifySubmitter($household, "Household has been rejected ({$household->notification_label}).");

                    return redirect()->route('households.show', $household)->with('success', 'Household has been rejected.');
                }

                // Super Admin rejects admin-approved records (Stage 2).
                if ($userRole === 'super_admin' && $household->approval_status === 'admin_approved') {
                    $household->update([
                        'approval_status' => 'rejected',
                        'admin_approved_by' => auth()->id(),
                        'admin_approved_at' => now(),
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                        'remarks' => $data['remarks'] ?? null,
                    ]);

                    AuditLogService::log('Reject Household', 'Household Management', "Rejected household: {$household->household_number}");

                    $this->notifySubmitter($household, "Household has been rejected ({$household->notification_label}).");
                    $this->notifyAdmins("Household was rejected ({$household->notification_label}).".($data['remarks'] ? ' Remarks: '.$data['remarks'] : ''), 'warning', $household->village_id);

                    return redirect()->route('households.show', $household)->with('success', 'Household has been rejected.');
                }

                return back()->with('error', $this->alreadyProcessed($household, $userRole)
                    ? 'This household request has already been processed.'
                    : 'You cannot reject this household at this stage.');
            }

            return back()->with('error', 'Invalid action.');
        });
    }

    /**
     * Whether the record has already been decided, so a repeated click must not
     * process the approval a second time.
     */
    private function alreadyProcessed(Household $household, ?string $userRole): bool
    {
        return in_array($household->approval_status, ['approved', 'rejected'], true)
            || ($userRole === 'admin' && $household->approval_status === 'admin_approved');
    }

    private function notifyAdmins(string $message, string $type = 'approval', ?int $villageId = null): void
    {
        try {
            app(NotificationService::class)->notifyAdmins($villageId, 'Approval Required', $message, route('approvals.households'), $type);
        } catch (\Throwable $e) {
        }
    }

    private function notifySuperAdmins(string $message): void
    {
        try {
            app(NotificationService::class)->notifySuperAdmins('Approval Update', $message, route('approvals.households'));
        } catch (\Throwable $e) {
        }
    }

    private function notifySubmitter(Household $household, string $message): void
    {
        try {
            if ($household->submittedBy) {
                app(NotificationService::class)->notifyUser($household->submittedBy->id, 'approval', 'Household Update', $message, route('households.show', $household));
            }
        } catch (\Throwable $e) {
        }
    }
}
