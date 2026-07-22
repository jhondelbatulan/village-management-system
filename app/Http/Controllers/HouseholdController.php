<?php

namespace App\Http\Controllers;

use App\Http\Requests\HouseholdRequest\ApproveHouseholdRequest;
use App\Http\Requests\HouseholdRequest\StoreHouseholdRequest;
use App\Http\Requests\HouseholdRequest\UpdateHouseholdRequest;
use App\Models\Household;
use App\Models\Resident;
use App\Models\User;
use App\Models\Village;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        } else {
            $query->latest();
        }

        $households = $query->paginate(15)->withQueryString();
        $archivedHouseholds = Household::onlyTrashed()->latest()->paginate(10);
        $villages = Village::where('status', 'active')->orderBy('village_name')->get();

        $blockNumbers = Household::where('block_number', '!=', null)
            ->where('block_number', '!=', '')
            ->distinct()
            ->pluck('block_number')
            ->sort()
            ->values();

        $lotNumbers = Household::where('lot_number', '!=', null)
            ->where('lot_number', '!=', '')
            ->distinct()
            ->pluck('lot_number')
            ->sort()
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

        return view('households.create', compact('villages'));
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
        if ($village) $village->recalculateCounts();

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

        if (!$user->household_id) {
            return back()->with('error', 'You are not assigned to any household.');
        }

        $household = Household::with(['village', 'householdHead', 'residents', 'submittedBy', 'approvedBy', 'adminApprovedBy', 'billings.paymentType', 'payments'])
            ->find($user->household_id);

        if (!$household) {
            return back()->with('error', 'Your household record no longer exists. Please contact an administrator.');
        }

        $paymentService = app(PaymentService::class);
        $balance = $paymentService->getHouseholdBalance($household->id);

        $billings = $paymentService->getHouseholdBillings($household->id);
        $payments = $paymentService->getHouseholdPayments($household->id);

        return view('households.my-household', compact('household', 'balance', 'billings', 'payments'));
    }

    public function edit(Household $household): View
    {
        $villages = Village::where('status', 'active')->orderBy('village_name')->get();

        return view('households.edit', compact('household', 'villages'));
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
        if ($village) $village->recalculateCounts();

        AuditLogService::log('Update Household', 'Household Management', "Updated household: {$household->household_number}");

        return redirect()->route('households.index')->with('success', 'Household updated successfully.');
    }

    public function destroy(Household $household): RedirectResponse
    {
        $number = $household->household_number;
        $household->delete();

        $village = Village::find($household->village_id);
        if ($village) $village->recalculateCounts();

        AuditLogService::log('Archive Household', 'Household Management', "Archived household: {$number}");

        return redirect()->route('households.index')->with('success', "Household \"{$number}\" has been archived.");
    }

    public function restore(int $id): RedirectResponse
    {
        $household = Household::withTrashed()->findOrFail($id);
        $household->restore();

        $village = Village::find($household->village_id);
        if ($village) $village->recalculateCounts();

        AuditLogService::log('Restore Household', 'Household Management', "Restored household: {$household->household_number}");

        return redirect()->route('households.index')->with('success', "Household \"{$household->household_number}\" has been restored.");
    }

    public function submit(Household $household): RedirectResponse
    {
        if (!in_array($household->approval_status, ['draft', 'rejected'])) {
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

        $this->notifyAdmins("New household {$household->household_number} submitted for approval.", 'approval', $household->village_id);

        return redirect()->route('households.show', $household)->with('success', 'Household submitted for approval successfully.');
    }

    public function requestMemberForm(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (!$user->household_id) {
            return redirect()->route('households.my-household')->with('error', 'You are not assigned to any household.');
        }

        return view('households.request-member');
    }

    public function requestMember(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (!$user->household_id) {
            return back()->with('error', 'You are not assigned to any household.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'gender' => 'required|in:Male,Female,Other',
            'birthdate' => 'required|date|before:today',
            'civil_status' => 'required|in:Single,Married,Widowed,Separated,Divorced',
            'nationality' => 'nullable|string|max:100',
            'religion' => 'nullable|string|max:100',
            'contact_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'relationship_to_head' => 'nullable|string|max:100',
            'voter_status' => 'nullable|boolean',
            'is_household_head' => 'nullable|boolean',

            // Education
            'educational_attainment' => 'nullable|string|max:100',
            'education_level' => 'nullable|string|max:100',
            'school_name' => 'nullable|string|max:255',
            'grade_level' => 'nullable|string|max:100',
            'is_scholarship' => 'nullable|boolean',
            'scholarship_type' => 'nullable|string|max:255',

            // Employment
            'employment_status' => 'nullable|string|max:50',
            'occupation' => 'nullable|string|max:255',
            'employer_name' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'years_of_experience' => 'nullable|integer|min:0|max:60',

            // Income
            'income_source' => 'nullable|string|max:100',
            'monthly_income' => 'nullable|numeric|min:0',
            'monthly_income_range' => 'nullable|string|max:100',

            // Special Population
            'is_pwd' => 'nullable|boolean',
            'pwd_type' => 'nullable|string|max:255',
            'is_solo_parent' => 'nullable|boolean',
            'is_senior_citizen' => 'nullable|boolean',
            'is_youth' => 'nullable|boolean',
            'is_student' => 'nullable|boolean',
            'is_ofw' => 'nullable|boolean',
            'is_infant' => 'nullable|boolean',

            // Government Memberships
            'is_pagibig_member' => 'nullable|boolean',
            'is_philhealth_member' => 'nullable|boolean',
            'is_sss_member' => 'nullable|boolean',
            'is_gsis_member' => 'nullable|boolean',

            // Government Assistance
            'is_4ps_member' => 'nullable|boolean',
            'is_senior_pensioner' => 'nullable|boolean',
            'other_gov_assistance' => 'nullable|string|max:255',
            'is_tupad_beneficiary' => 'nullable|boolean',
            'is_akap_beneficiary' => 'nullable|boolean',
            'is_educational_assistance' => 'nullable|boolean',
            'is_emergency_assistance' => 'nullable|boolean',
            'is_slp_beneficiary' => 'nullable|boolean',

            // Government IDs
            'is_national_id_holder' => 'nullable|boolean',
            'is_senior_id_holder' => 'nullable|boolean',
            'is_pwd_id_holder' => 'nullable|boolean',
            'is_solo_parent_id_holder' => 'nullable|boolean',

            // Social Categories
            'is_indigenous' => 'nullable|boolean',
            'is_veteran' => 'nullable|boolean',

            // Community Participation
            'is_disaster_volunteer' => 'nullable|boolean',
            'is_village_volunteer' => 'nullable|boolean',

            // Skills & Livelihood
            'skills' => 'nullable|string',
            'primary_skill' => 'nullable|string|max:255',
            'secondary_skill' => 'nullable|string|max:255',
            'livelihood_programs' => 'nullable|string',
            'special_qualifications' => 'nullable|string',
            'community_activities' => 'nullable|string',

            // Emergency Contact
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_number' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',

            // Additional
            'blood_type' => 'nullable|string|max:10',
            'years_of_residency' => 'nullable|integer|min:0',
        ]);

        $household = Household::findOrFail($user->household_id);

        $data = array_merge($validated, [
            'resident_code' => Resident::generateResidentCode(),
            'household_id' => $household->id,
            'village_id' => $household->village_id,
            'approval_status' => 'pending',
            'submitted_by' => $user->id,
            'status' => 'active',
        ]);

        $resident = Resident::create($data);

        $this->notifyAdmins("New resident {$resident->full_name} requested to join household {$household->household_number}.", 'approval', $household->village_id);

        AuditLogService::log('Request Add Member', 'Household Management', "Resident requested to add {$resident->full_name} to household {$household->household_number}");

        return redirect()->route('households.my-household')->with('success', 'Your request to add a new member has been submitted for secretary approval.');
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
        if ($village) $village->recalculateCounts();

        AuditLogService::log('Toggle Household Status', 'Household Management', "Toggled household {$household->household_number} to {$newStatus}");

        return redirect()->back()->with('success', "Household status changed to {$newStatus}." . ($newStatus === 'inactive' ? ' All linked user accounts have been deactivated.' : ''));
    }

    public function approve(Household $household, ApproveHouseholdRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $action = $data['approval_status'];
        $userRole = auth()->user()->role?->role_slug;

        if ($action === 'approved') {
            if ($userRole === 'admin' && $household->approval_status === 'pending') {
                $household->update([
                    'approval_status' => 'admin_approved',
                    'admin_approved_by' => auth()->id(),
                    'admin_approved_at' => now(),
                    'remarks' => $data['remarks'] ?? null,
                ]);

                AuditLogService::log('Admin Approve Household', 'Household Management', "Admin approved household: {$household->household_number} (pending super admin final approval)");

                $this->notifySuperAdmins("Household {$household->household_number} approved by Admin. Pending your final approval.");
                $this->notifySubmitter($household, "Household {$household->household_number} approved by Admin. Awaiting Super Admin final approval.");

                return redirect()->route('households.show', $household)->with('success', 'Household approved by Admin. Awaiting Super Admin final approval.');
            }

            if ($userRole === 'super_admin' && in_array($household->approval_status, ['pending', 'admin_approved'])) {
                $household->update([
                    'approval_status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'remarks' => $data['remarks'] ?? null,
                ]);

                AuditLogService::log('Final Approve Household', 'Household Management', "Super Admin final approved household: {$household->household_number}");

                $this->notifySubmitter($household, "Household {$household->household_number} has been approved by Super Admin.");

                return redirect()->route('households.show', $household)->with('success', 'Household has been approved successfully.');
            }

            return back()->with('error', 'You cannot approve this household at this stage.');
        }

        if ($action === 'rejected') {
            if (in_array($userRole, ['admin', 'super_admin']) && in_array($household->approval_status, ['pending', 'admin_approved'])) {
                $household->update([
                    'approval_status' => 'rejected',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'remarks' => $data['remarks'] ?? null,
                ]);

                AuditLogService::log('Reject Household', 'Household Management', "Rejected household: {$household->household_number}");

                $this->notifySubmitter($household, "Household {$household->household_number} has been rejected.");

                return redirect()->route('households.show', $household)->with('success', 'Household has been rejected.');
            }

            return back()->with('error', 'You cannot reject this household at this stage.');
        }

        return back()->with('error', 'Invalid action.');
    }

    private function notifyAdmins(string $message, string $type, ?int $villageId = null): void
    {
        try {
            $service = app(NotificationService::class);
            $query = User::whereHas('role', fn($q) => $q->where('role_slug', 'admin'))->where('status', 'active');
            if ($villageId) {
                $query->where('village_id', $villageId);
            }
            foreach ($query->get() as $user) {
                $service->createFromPlaceholder($type, 'Approval Required', $message, route('approvals.households'), $user->id);
            }
        } catch (\Throwable $e) {}
    }

    private function notifySuperAdmins(string $message): void
    {
        try {
            $service = app(NotificationService::class);
            $users = User::whereHas('role', fn($q) => $q->where('role_slug', 'super_admin'))->where('status', 'active')->get();
            foreach ($users as $user) {
                $service->createFromPlaceholder('approval', 'Approval Update', $message, route('approvals.households'), $user->id);
            }
        } catch (\Throwable $e) {}
    }

    private function notifySubmitter(Household $household, string $message): void
    {
        try {
            if ($household->submittedBy) {
                $service = app(NotificationService::class);
                $service->createFromPlaceholder('approval', 'Household Update', $message, route('households.show', $household), $household->submittedBy->id);
            }
        } catch (\Throwable $e) {}
    }
}
