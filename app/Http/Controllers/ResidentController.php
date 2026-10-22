<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResidentRequest\ApproveResidentRequest;
use App\Http\Requests\ResidentRequest\StoreResidentRequest;
use App\Http\Requests\ResidentRequest\UpdateResidentRequest;
use App\Models\Household;
use App\Models\Resident;
use App\Models\Village;
use App\Rules\ProperName;
use App\Rules\SafeText;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResidentController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $query = Resident::with(['village', 'household', 'submittedBy', 'approvedBy', 'adminApprovedBy']);

        if (in_array($user->role?->role_slug, ['admin', 'secretary', 'treasurer'])) {
            $query->where('village_id', $user->village_id);
        }

        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('resident_code', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (request('village_id')) {
            $query->where('village_id', request('village_id'));
        }

        if (request('gender')) {
            $query->where('gender', request('gender'));
        }

        if (request('status')) {
            $query->where('status', request('status'));
        }

        if (request('approval_status')) {
            $query->where('approval_status', request('approval_status'));
        } else {
            $query->where('approval_status', 'approved');
        }

        if (request('civil_status')) {
            $query->where('civil_status', request('civil_status'));
        }

        if (request('household_id')) {
            $query->where('household_id', request('household_id'));
        }

        if (request('block_number')) {
            $query->whereHas('household', fn ($q) => $q->where('block_number', request('block_number')));
        }

        if (request('lot_number')) {
            $query->whereHas('household', fn ($q) => $q->where('lot_number', request('lot_number')));
        }

        if (request('sort') === 'az') {
            $query->orderBy('last_name')->orderBy('first_name');
        } elseif (request('sort') === 'za') {
            $query->orderByDesc('last_name')->orderByDesc('first_name');
        } else {
            $query->latest();
        }

        $residents = $query->paginate(15)->withQueryString();
        $archivedResidents = Resident::onlyTrashed()->latest()->paginate(10);
        $villages = Village::where('status', 'active')->orderBy('village_name')->get();

        $user = auth()->user();
        $householdQuery = Household::where('block_number', '!=', null)
            ->where('block_number', '!=', '')
            ->distinct()
            ->select('block_number');

        if (in_array($user->role?->role_slug, ['admin', 'secretary', 'treasurer'])) {
            $householdQuery->where('village_id', $user->village_id);
        }

        $blockNumbers = $householdQuery->pluck('block_number')->sort()->values();

        $lotQuery = Household::where('lot_number', '!=', null)
            ->where('lot_number', '!=', '')
            ->distinct()
            ->select('lot_number');

        if (in_array($user->role?->role_slug, ['admin', 'secretary', 'treasurer'])) {
            $lotQuery->where('village_id', $user->village_id);
        }

        $lotNumbers = $lotQuery->pluck('lot_number')->sort()->values();

        return view('residents.index', compact('residents', 'archivedResidents', 'villages', 'blockNumbers', 'lotNumbers'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $villages = Village::where('status', 'active')->orderBy('village_name')->get();
        $nextResidentCode = Resident::generateResidentCode();

        if (in_array($user->role?->role_slug, ['admin', 'secretary', 'treasurer'])) {
            $villages = $villages->where('id', $user->village_id);
        }

        $householdsQuery = Household::query()
            ->orderBy('block_number')
            ->orderBy('lot_number');

        if (in_array($user->role?->role_slug, ['admin', 'secretary', 'treasurer'])) {
            $householdsQuery->where('village_id', $user->village_id);
        }

        $households = $householdsQuery->get();

        $hasHouseholds = $households->isNotEmpty();

        return view('residents.create', compact('villages', 'households', 'nextResidentCode', 'hasHouseholds'));
    }

    public function store(StoreResidentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['resident_code'] = Resident::generateResidentCode();
        $data['nationality'] = $data['nationality'] ?: 'Filipino';
        $data['is_household_head'] = $request->boolean('is_household_head');
        $data['approval_status'] = 'draft';
        $data['submitted_by'] = null;

        $household = Household::find($data['household_id']);
        if ($household) {
            $data['village_id'] = $household->village_id;
        }

        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $filename = 'resident_'.time().'.'.$file->getClientOriginalExtension();
            $file->storeAs('residents', $filename, 'public');
            $data['profile_picture'] = 'residents/'.$filename;
        }

        $resident = Resident::create($data);

        $village = Village::find($data['village_id']);
        if ($village) {
            $village->recalculateCounts();
        }

        AuditLogService::log('Create Resident', 'Resident Management', "Registered resident: {$resident->full_name} ({$resident->resident_code})");

        return redirect()->route('residents.index')->with('success', "Resident {$resident->resident_code} registered successfully. Please submit it for approval.");
    }

    public function show(Resident $resident): View
    {
        $resident->load(['village', 'household.residents', 'submittedBy', 'approvedBy', 'adminApprovedBy']);

        return view('residents.show', compact('resident'));
    }

    public function edit(Resident $resident): View
    {
        $user = auth()->user();
        $villages = Village::where('status', 'active')->orderBy('village_name')->get();
        $households = Household::query()
            ->where('village_id', $resident->village_id)
            ->orderBy('block_number')
            ->orderBy('lot_number')
            ->get();

        // The household the resident already belongs to must always be selectable,
        // even when it sits outside the village scope or was archived.
        if ($resident->household_id && ! $households->contains('id', $resident->household_id)) {
            $currentHousehold = $resident->household()->withTrashed()->first();

            if ($currentHousehold) {
                $households->prepend($currentHousehold);
            }
        }

        if (in_array($user->role?->role_slug, ['admin', 'secretary', 'treasurer'])) {
            $villages = $villages->where('id', $user->village_id);
        }

        return view('residents.edit', compact('resident', 'villages', 'households'));
    }

    public function update(UpdateResidentRequest $request, Resident $resident): RedirectResponse
    {
        $data = $request->validated();
        $data['nationality'] = $data['nationality'] ?: 'Filipino';
        $data['is_household_head'] = $request->boolean('is_household_head');

        if ($resident->approval_status === 'rejected') {
            $data['approval_status'] = 'draft';
            $data['submitted_by'] = null;
            $data['approved_by'] = null;
            $data['approved_at'] = null;
            $data['admin_approved_by'] = null;
            $data['admin_approved_at'] = null;
            $data['remarks'] = null;
        }

        if ($request->hasFile('profile_picture')) {
            if ($resident->profile_picture) {
                $oldPath = storage_path('app/public/'.$resident->profile_picture);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            $file = $request->file('profile_picture');
            $filename = 'resident_'.$resident->id.'_'.time().'.'.$file->getClientOriginalExtension();
            $file->storeAs('residents', $filename, 'public');
            $data['profile_picture'] = 'residents/'.$filename;
        }

        $household = Household::find($data['household_id'] ?? $resident->household_id);
        if ($household) {
            $data['village_id'] = $household->village_id;
        }

        $oldVillageId = $resident->village_id;
        $resident->update($data);

        $newVillage = Village::find($resident->village_id);
        if ($newVillage) {
            $newVillage->recalculateCounts();
        }

        if ($oldVillageId != $resident->village_id) {
            $oldVillage = Village::find($oldVillageId);
            if ($oldVillage) {
                $oldVillage->recalculateCounts();
            }
        }

        AuditLogService::log('Update Resident', 'Resident Management', "Updated resident: {$resident->full_name}");

        return redirect()->route('residents.index')->with('success', 'Resident updated successfully.');
    }

    public function destroy(Resident $resident): RedirectResponse
    {
        $name = $resident->full_name;
        $code = $resident->resident_code;
        $resident->delete();

        $village = Village::find($resident->village_id);
        if ($village) {
            $village->recalculateCounts();
        }

        AuditLogService::log('Archive Resident', 'Resident Management', "Archived resident: {$name} ({$code})");

        if ($resident->household_id) {
            app(NotificationService::class)->notifyHouseholdResidents(
                [$resident->household_id],
                'warning',
                'Resident Archived',
                "A resident profile ({$name}) in your household has been archived.",
                route('households.my-household')
            );
        }

        return redirect()->route('residents.index')->with('success', "Resident \"{$name}\" has been archived.");
    }

    public function restore(int $id): RedirectResponse
    {
        $resident = Resident::withTrashed()->findOrFail($id);
        $resident->restore();

        $village = Village::find($resident->village_id);
        if ($village) {
            $village->recalculateCounts();
        }

        AuditLogService::log('Restore Resident', 'Resident Management', "Restored resident: {$resident->full_name}");

        return redirect()->route('residents.index')->with('success', "Resident \"{$resident->full_name}\" has been restored.");
    }

    public function submit(Resident $resident): RedirectResponse
    {
        if (! in_array($resident->approval_status, ['draft', 'rejected'])) {
            return back()->with('error', 'Only draft or rejected records can be submitted for approval.');
        }

        $data = [
            'approval_status' => 'pending',
            'submitted_by' => auth()->id(),
        ];

        if ($resident->approval_status === 'rejected') {
            $data['approved_by'] = null;
            $data['approved_at'] = null;
            $data['admin_approved_by'] = null;
            $data['admin_approved_at'] = null;
            $data['remarks'] = null;
        }

        $resident->update($data);

        AuditLogService::log('Submit Resident', 'Resident Management', "Submitted resident for approval: {$resident->full_name}");

        $householdLabel = $resident->household?->notification_label;

        $this->notifyAdmins(
            $householdLabel
                ? "New resident {$resident->full_name} is added on the household ({$householdLabel})."
                : "New resident {$resident->full_name} submitted for approval.",
            'approval',
            $resident->village_id
        );

        return redirect()->route('residents.show', $resident)->with('success', 'Resident submitted for approval successfully.');
    }

    public function approve(Resident $resident, ApproveResidentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        return DB::transaction(function () use ($resident, $data): RedirectResponse {
            // Lock the row so only one approval action can be processed at a time.
            $resident = Resident::query()->lockForUpdate()->findOrFail($resident->id);

            $action = $data['approval_status'];
            $userRole = auth()->user()->role?->role_slug;

            if ($action === 'approved') {
                // Stage 1 — Admin reviews pending submissions from their village.
                if ($userRole === 'admin' && $resident->approval_status === 'pending') {
                    $resident->update([
                        'approval_status' => 'admin_approved',
                        'admin_approved_by' => auth()->id(),
                        'admin_approved_at' => now(),
                        'remarks' => $data['remarks'] ?? null,
                    ]);

                    AuditLogService::log('Admin Approve Resident', 'Resident Management', "Admin approved resident: {$resident->full_name} (pending super admin final approval)");

                    $this->notifySuperAdmins("Resident {$resident->full_name} approved by Admin. Pending your final approval.");
                    $this->notifySubmitter($resident, "Resident {$resident->full_name} approved by Admin. Awaiting Super Admin final approval.");

                    return redirect()->route('residents.show', $resident)->with('success', 'Resident approved by Admin. Awaiting Super Admin final approval.');
                }

                // Stage 2 — Super Admin gives the final approval of admin-approved records only.
                if ($userRole === 'super_admin' && $resident->approval_status === 'admin_approved') {
                    $resident->update([
                        'approval_status' => 'approved',
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                        'remarks' => $data['remarks'] ?? null,
                    ]);

                    AuditLogService::log('Final Approve Resident', 'Resident Management', "Super Admin final approved resident: {$resident->full_name}");

                    $this->notifySubmitter($resident, "Resident {$resident->full_name} has been approved by Super Admin.");

                    return redirect()->route('residents.show', $resident)->with('success', 'Resident has been approved successfully.');
                }

                return back()->with('error', $this->alreadyProcessed($resident, $userRole)
                    ? 'This resident request has already been processed.'
                    : 'You cannot approve this resident at this stage.');
            }

            if ($action === 'rejected') {
                // Admin rejects pending submissions in their village (Stage 1).
                if ($userRole === 'admin' && $resident->approval_status === 'pending') {
                    $resident->update([
                        'approval_status' => 'rejected',
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                        'remarks' => $data['remarks'] ?? null,
                    ]);

                    AuditLogService::log('Reject Resident', 'Resident Management', "Rejected resident: {$resident->full_name}");

                    $this->notifySubmitter($resident, "Resident {$resident->full_name} has been rejected.");

                    return redirect()->route('residents.show', $resident)->with('success', 'Resident has been rejected.');
                }

                // Super Admin rejects admin-approved records (Stage 2).
                if ($userRole === 'super_admin' && $resident->approval_status === 'admin_approved') {
                    $resident->update([
                        'approval_status' => 'rejected',
                        'admin_approved_by' => auth()->id(),
                        'admin_approved_at' => now(),
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                        'remarks' => $data['remarks'] ?? null,
                    ]);

                    AuditLogService::log('Reject Resident', 'Resident Management', "Rejected resident: {$resident->full_name}");

                    $this->notifySubmitter($resident, "Resident {$resident->full_name} has been rejected.");
                    $this->notifyAdmins("Resident {$resident->full_name} was rejected.".($data['remarks'] ? ' Remarks: '.$data['remarks'] : ''), 'warning', $resident->village_id);

                    return redirect()->route('residents.show', $resident)->with('success', 'Resident has been rejected.');
                }

                return back()->with('error', $this->alreadyProcessed($resident, $userRole)
                    ? 'This resident request has already been processed.'
                    : 'You cannot reject this resident at this stage.');
            }

            return back()->with('error', 'Invalid action.');
        });
    }

    /**
     * Whether the record has already been decided, so a repeated click must not
     * process the approval a second time.
     */
    private function alreadyProcessed(Resident $resident, ?string $userRole): bool
    {
        return in_array($resident->approval_status, ['approved', 'rejected'], true)
            || ($userRole === 'admin' && $resident->approval_status === 'admin_approved');
    }

    public function import(): View
    {
        $user = auth()->user();
        $role = $user->role?->role_slug ?? '';

        if ($role === 'secretary' && $user->village_id) {
            $villages = Village::where('id', $user->village_id)->where('status', 'active')->get();
        } else {
            $villages = Village::where('status', 'active')->orderBy('village_name')->get();
        }

        return view('residents.import', compact('villages'));
    }

    public function processImport(Request $request): RedirectResponse
    {
        $request->validate([
            'village_id' => ['required', 'exists:villages,id'],
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        $user = auth()->user();
        $villageId = (int) $request->village_id;

        if (($user->role?->role_slug ?? '') !== 'super_admin') {
            if ($user->village_id && $user->village_id != $villageId) {
                abort(403, 'You do not have access to import residents to this village.');
            }
        }

        $file = $request->file('csv_file');
        $villageId = $request->village_id;
        $rows = [];
        $errors = [];
        $imported = 0;
        $lineNumber = 0;

        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;
            $data = array_combine($header, $row);

            $validation = Validator::make($data, [
                'first_name' => ['required', 'string', 'max:255', new ProperName],
                'middle_name' => ['nullable', 'string', 'max:255', new ProperName],
                'last_name' => ['required', 'string', 'max:255', new ProperName],
                'suffix' => ['nullable', 'string', 'max:50', new SafeText],
                'gender' => 'required|in:Male,Female,Other',
                'birthdate' => 'required|date|before:today',
                'civil_status' => 'required|in:Single,Married,Widowed,Separated,Divorced',
            ]);

            if ($validation->fails()) {
                $errors[] = "Line {$lineNumber}: ".implode(', ', $validation->errors()->all());

                continue;
            }

            $residentCode = Resident::generateResidentCode();

            Resident::create([
                'village_id' => $villageId,
                'resident_code' => $residentCode,
                'first_name' => $data['first_name'] ?? '',
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'] ?? '',
                'suffix' => $data['suffix'] ?? null,
                'gender' => $data['gender'] ?? 'Male',
                'birthdate' => $data['birthdate'] ?? null,
                'civil_status' => $data['civil_status'] ?? 'Single',
                'nationality' => $data['nationality'] ?? 'Filipino',
                'occupation' => $data['occupation'] ?? null,
                'educational_attainment' => $data['educational_attainment'] ?? null,
                'contact_number' => $data['contact_number'] ?? null,
                'email' => $data['email'] ?? null,
                'status' => 'active',
                'approval_status' => 'draft',
            ]);

            $imported++;
        }

        fclose($handle);

        $village = Village::find($villageId);
        if ($village) {
            $village->recalculateCounts();
        }

        AuditLogService::log('Import Residents', 'Resident Management', "Imported {$imported} residents");

        $message = "{$imported} residents imported successfully.";
        if (! empty($errors)) {
            $errorCount = count($errors);
            $message .= " {$errorCount} rows had errors.";
            session(['import_errors' => $errors]);
        }

        return redirect()->route('residents.index')->with('success', $message);
    }

    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="resident_import_template.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'first_name', 'middle_name', 'last_name', 'suffix', 'gender',
                'birthdate', 'civil_status', 'nationality', 'occupation',
                'educational_attainment', 'contact_number', 'email',
            ]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function export(Request $request): StreamedResponse
    {
        $user = auth()->user();
        $query = Resident::with(['village', 'household']);

        if (in_array($user->role?->role_slug, ['admin', 'secretary', 'treasurer'])) {
            $query->where('village_id', $user->village_id);
        }

        if (request('village_id')) {
            $query->where('village_id', request('village_id'));
        }

        if (request('status')) {
            $query->where('status', request('status'));
        }

        $query->where('approval_status', 'approved');

        $residents = $query->latest()->get();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="residents_export_'.now()->format('Y-m-d').'.csv"',
        ];

        $callback = function () use ($residents) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Resident Code', 'First Name', 'Middle Name', 'Last Name', 'Suffix',
                'Gender', 'Birthdate', 'Age', 'Civil Status', 'Nationality',
                'Occupation', 'Education', 'Religion', 'Contact', 'Email',
                'Village', 'Household', 'Relationship', 'Voter Status', 'Status',
            ]);

            foreach ($residents as $r) {
                fputcsv($file, [
                    $r->resident_code, $r->first_name, $r->middle_name, $r->last_name, $r->suffix,
                    $r->gender, $r->birthdate?->format('Y-m-d'), $r->age, $r->civil_status, $r->nationality,
                    $r->occupation, $r->educational_attainment, $r->religion, $r->contact_number, $r->email,
                    $r->village?->village_name, $r->household?->household_number, $r->relationship_to_head,
                    $r->voter_status ? 'Yes' : 'No', $r->status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportHouseholds(Request $request): StreamedResponse
    {
        $user = auth()->user();
        $query = Household::with('village');

        if (in_array($user->role?->role_slug, ['admin', 'secretary', 'treasurer'])) {
            $query->where('village_id', $user->village_id);
        }

        if (request('village_id')) {
            $query->where('village_id', request('village_id'));
        }

        $households = $query->latest()->get();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="households_export_'.now()->format('Y-m-d').'.csv"',
        ];

        $callback = function () use ($households) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Household Number', 'Block', 'Lot', 'Village', 'Purok', 'Address', 'Contact',
                'Type', 'Ownership', 'Status', 'Approval Status', 'Registration Date',
            ]);

            foreach ($households as $h) {
                fputcsv($file, [
                    $h->household_number, $h->block_number, $h->lot_number,
                    $h->village?->village_name, $h->purok, $h->address,
                    $h->contact_number, $h->household_type, $h->house_ownership,
                    $h->status, $h->approval_status, $h->registration_date?->format('Y-m-d'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportVillages(): StreamedResponse
    {
        $villages = Village::withTrashed()->latest()->get();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="villages_export_'.now()->format('Y-m-d').'.csv"',
        ];

        $callback = function () use ($villages) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Village Code', 'Village Name', 'Address', 'Contact', 'Email',
                'Total Area', 'Established', 'Total Households', 'Total Population', 'Status',
            ]);

            foreach ($villages as $v) {
                fputcsv($file, [
                    $v->village_code, $v->village_name, $v->address, $v->contact_number,
                    $v->email, $v->total_area, $v->established_date?->format('Y-m-d'),
                    $v->total_households, $v->total_population, $v->status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function notifyAdmins(string $message, string $type = 'approval', ?int $villageId = null): void
    {
        try {
            app(NotificationService::class)->notifyAdmins($villageId, 'Approval Required', $message, route('approvals.residents'), $type);
        } catch (\Throwable $e) {
        }
    }

    private function notifySuperAdmins(string $message): void
    {
        try {
            app(NotificationService::class)->notifySuperAdmins('Approval Update', $message, route('approvals.residents'));
        } catch (\Throwable $e) {
        }
    }

    private function notifySubmitter(Resident $resident, string $message): void
    {
        try {
            if ($resident->submittedBy) {
                app(NotificationService::class)->notifyUser($resident->submittedBy->id, 'approval', 'Resident Update', $message, route('residents.show', $resident));
            }
        } catch (\Throwable $e) {
        }
    }
}
