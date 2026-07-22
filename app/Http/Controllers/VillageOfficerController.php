<?php

namespace App\Http\Controllers;

use App\Http\Requests\VillageRequest\StoreVillageOfficerRequest;
use App\Http\Requests\VillageRequest\UpdateVillageOfficerRequest;
use App\Models\Village;
use App\Models\VillageOfficer;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VillageOfficerController extends Controller
{
    public function index(Village $village): View
    {
        $officers = $village->officers()->latest()->paginate(15);

        return view('villages.officers', compact('village', 'officers'));
    }

    public function store(Village $village, StoreVillageOfficerRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['village_id'] = $village->id;

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'officer_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('officer_photos', $filename, 'public');
            $data['photo'] = 'officer_photos/' . $filename;
        }

        VillageOfficer::create($data);

        AuditLogService::log('Add Officer', 'Village Officers', "Added officer to {$village->village_name}: {$data['full_name']}");

        return redirect()->route('villages.officers.index', $village)->with('success', 'Officer added successfully.');
    }

    public function update(Village $village, VillageOfficer $officer, UpdateVillageOfficerRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            if ($officer->photo) {
                $oldPath = storage_path('app/public/' . $officer->photo);
                if (file_exists($oldPath)) unlink($oldPath);
            }
            $file = $request->file('photo');
            $filename = 'officer_' . $officer->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('officer_photos', $filename, 'public');
            $data['photo'] = 'officer_photos/' . $filename;
        }

        $officer->update($data);

        AuditLogService::log('Update Officer', 'Village Officers', "Updated officer: {$officer->full_name}");

        return redirect()->route('villages.officers.index', $village)->with('success', 'Officer updated successfully.');
    }

    public function destroy(Village $village, VillageOfficer $officer): RedirectResponse
    {
        $name = $officer->full_name;
        $officer->delete();

        AuditLogService::log('Remove Officer', 'Village Officers', "Removed officer from {$village->village_name}: {$name}");

        return redirect()->route('villages.officers.index', $village)->with('success', "Officer \"{$name}\" has been removed.");
    }
}
