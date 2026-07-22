<?php

namespace App\Http\Controllers;

use App\Http\Requests\VillageRequest\StoreVillageRequest;
use App\Http\Requests\VillageRequest\UpdateVillageRequest;
use App\Models\Village;
use App\Models\VillagePhoto;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class VillageController extends Controller
{
    public function index(): View
    {
        $query = Village::query();

        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('village_name', 'like', "%{$search}%")
                  ->orWhere('village_code', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if (request('status')) {
            $query->where('status', request('status'));
        }

        $villages = $query->latest()->paginate(15)->withQueryString();
        $archivedVillages = Village::onlyTrashed()->latest()->paginate(10);

        return view('villages.index', compact('villages', 'archivedVillages'));
    }

    public function create(): View
    {
        return view('villages.create');
    }

    public function store(StoreVillageRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'village_logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('village_logos', $filename, 'public');
            $data['logo'] = 'village_logos/' . $filename;
        }

        if ($request->hasFile('cover_photo')) {
            $file = $request->file('cover_photo');
            $filename = 'village_cover_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('village_covers', $filename, 'public');
            $data['cover_photo'] = 'village_covers/' . $filename;
        }

        $village = Village::create($data);

        AuditLogService::log('Create Village', 'Village Management', "Created village: {$village->village_name}");

        return redirect()->route('villages.index')->with('success', 'Village created successfully.');
    }

    public function show(Village $village): View
    {
        $village->load(['officers', 'documents' => function ($q) {
            $q->latest();
        }, 'photos']);

        $stats = [
            'total_households' => $village->households()->count(),
            'total_residents' => $village->residents()->where('status', 'active')->count(),
            'active_officers' => $village->officers()->where('status', 'active')->count(),
            'total_documents' => $village->documents()->count(),
        ];

        $role = auth()->user()->role?->role_slug;
        $canEdit = in_array($role, ['super_admin', 'secretary']);

        return view('villages.show', compact('village', 'stats', 'canEdit'));
    }

    public function edit(Village $village): View
    {
        $village->load('photos');
        return view('villages.edit', compact('village'));
    }

    public function update(UpdateVillageRequest $request, Village $village): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            if ($village->logo) {
                $oldPath = storage_path('app/public/' . $village->logo);
                if (file_exists($oldPath)) unlink($oldPath);
            }
            $file = $request->file('logo');
            $filename = 'village_logo_' . $village->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('village_logos', $filename, 'public');
            $data['logo'] = 'village_logos/' . $filename;
        }

        if ($request->hasFile('cover_photo')) {
            if ($village->cover_photo) {
                $oldPath = storage_path('app/public/' . $village->cover_photo);
                if (file_exists($oldPath)) unlink($oldPath);
            }
            $file = $request->file('cover_photo');
            $filename = 'village_cover_' . $village->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('village_covers', $filename, 'public');
            $data['cover_photo'] = 'village_covers/' . $filename;
        }

        $village->update($data);

        AuditLogService::log('Update Village', 'Village Management', "Updated village: {$village->village_name}");

        $redirectTo = auth()->user()->role?->role_slug === 'secretary'
            ? route('villages.show', $village)
            : route('villages.index');

        return redirect($redirectTo)->with('success', 'Village updated successfully.');
    }

    public function destroy(Village $village): RedirectResponse
    {
        $name = $village->village_name;
        $village->delete();

        AuditLogService::log('Archive Village', 'Village Management', "Archived village: {$name}");

        return redirect()->route('villages.index')->with('success', "Village \"{$name}\" has been archived.");
    }

    public function restore(int $id): RedirectResponse
    {
        $village = Village::withTrashed()->findOrFail($id);
        $village->restore();

        AuditLogService::log('Restore Village', 'Village Management', "Restored village: {$village->village_name}");

        return redirect()->route('villages.index')->with('success', "Village \"{$village->village_name}\" has been restored.");
    }

    public function uploadPhoto(Request $request, Village $village): RedirectResponse
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $existingCount = $village->photos()->count();
        if ($existingCount >= 3) {
            return back()->with('error', 'Maximum of 3 photos per village reached.');
        }

        $file = $request->file('photo');
        $filename = 'village_photo_' . $village->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('village_photos', $filename, 'public');

        $village->photos()->create([
            'photo_path' => 'village_photos/' . $filename,
            'sort_order' => $existingCount,
        ]);

        AuditLogService::log('Upload Village Photo', 'Village Management', "Photo added to village: {$village->village_name}");

        return back()->with('success', 'Village photo added.');
    }

    public function deletePhoto(Village $village, VillagePhoto $photo): RedirectResponse
    {
        if ($photo->village_id !== $village->id) {
            abort(404);
        }

        $oldPath = storage_path('app/public/' . $photo->photo_path);
        if (file_exists($oldPath)) unlink($oldPath);

        $photo->delete();

        AuditLogService::log('Delete Village Photo', 'Village Management', "Photo removed from village: {$village->village_name}");

        return back()->with('success', 'Village photo deleted.');
    }
}
