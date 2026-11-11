<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Village;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AnnouncementService
{
    public function getAnnouncementsForUser($user, ?string $search = null, ?string $status = null, ?string $category = null, ?string $type = null, ?string $sort = null, ?string $creator = null)
    {
        $query = Announcement::with(['village', 'creator.role']);

        $role = $user->role?->role_slug;

        if ($role === 'super_admin') {
            // Super Admin sees ALL announcements across all villages
        } elseif (in_array($role, ['admin', 'secretary', 'treasurer'])) {
            $query->forVillage($user->village_id);
        } elseif ($role === 'resident') {
            $query->forVillage($user->village_id)->active();
        }

        $query->visibleToRole($role, $user->id);

        if ($creator === 'super_admin' || $creator === 'admin') {
            $query->whereHas('creator.role', fn ($q) => $q->where('role_slug', $creator));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($category) {
            $query->where('category', $category);
        }

        if ($type) {
            $query->where('announcement_type', $type);
        }

        if ($sort === 'az') {
            $query->orderBy('title');
        } elseif ($sort === 'za') {
            $query->orderByDesc('title');
        } else {
            $query->latest();
        }

        return $query->paginate(15)->withQueryString();
    }

    public function getActiveAnnouncementsForResident($user)
    {
        $villageId = $user->village_id;
        $householdId = $user->household_id;

        $query = Announcement::active()
            ->orderBy('is_pinned', 'desc')
            ->latest();

        if ($villageId) {
            $query->where(function ($q) use ($villageId, $householdId) {
                $q->where('village_id', $villageId)
                  ->orWhereNull('village_id')
                  ->orWhereRaw("JSON_CONTAINS(target_villages, ?)", [json_encode($villageId)]);
            });
        }

        $announcements = $query->get();

        return $announcements->filter(function ($announcement) use ($villageId, $householdId) {
            return $announcement->isVisibleToResident($villageId, $householdId);
        });
    }

    public function getActiveAnnouncements(int $villageId)
    {
        return Announcement::forVillage($villageId)
            ->active()
            ->orderBy('is_pinned', 'desc')
            ->latest()
            ->get();
    }

    public function getPinnedAnnouncement(int $villageId): ?Announcement
    {
        return Announcement::forVillage($villageId)
            ->active()
            ->pinned()
            // A village-specific pin wins over an "All Villages" pin.
            ->orderByRaw('village_id IS NULL')
            ->first();
    }

    public function create(array $data): Announcement
    {
        if (isset($data['cover_image']) && $data['cover_image'] instanceof UploadedFile) {
            $data['cover_image'] = $data['cover_image']->store('announcements', 'public');
        }

        if (isset($data['attachment']) && $data['attachment'] instanceof UploadedFile) {
            $data['attachment'] = $data['attachment']->store('announcements', 'public');
        }

        $data['created_by'] = auth()->id();

        $this->normalizeVisibleRoles($data);

        if (isset($data['is_pinned']) && $data['is_pinned']) {
            $this->clearPinned($data['village_id']);
        }

        return Announcement::create($data);
    }

    public function update(Announcement $announcement, array $data): Announcement
    {
        if (isset($data['cover_image']) && $data['cover_image'] instanceof UploadedFile) {
            if ($announcement->cover_image) {
                Storage::disk('public')->delete($announcement->cover_image);
            }
            $data['cover_image'] = $data['cover_image']->store('announcements', 'public');
        } else {
            unset($data['cover_image']);
        }

        if (isset($data['attachment']) && $data['attachment'] instanceof UploadedFile) {
            if ($announcement->attachment) {
                Storage::disk('public')->delete($announcement->attachment);
            }
            $data['attachment'] = $data['attachment']->store('announcements', 'public');
        } else {
            unset($data['attachment']);
        }

        if (isset($data['is_pinned']) && $data['is_pinned']) {
            $this->clearPinned($announcement->village_id, $announcement->id);
        }

        $this->normalizeVisibleRoles($data);

        $announcement->update($data);

        return $announcement;
    }

    public function delete(Announcement $announcement): void
    {
        if ($announcement->cover_image) {
            Storage::disk('public')->delete($announcement->cover_image);
        }
        if ($announcement->attachment) {
            Storage::disk('public')->delete($announcement->attachment);
        }

        $announcement->delete();
    }

    public function publish(Announcement $announcement): void
    {
        $announcement->update(['status' => 'published']);
    }

    public function archive(Announcement $announcement): void
    {
        $announcement->update(['status' => 'archived']);
    }

    private function clearPinned(?int $villageId, ?int $exceptId = null): void
    {
        // One pinned announcement per scope: village pins and the "All
        // Villages" pin are cleared separately so pinning one village's
        // announcement never silently unpins another village's.
        $query = Announcement::where('is_pinned', true);

        if ($villageId === null) {
            $query->whereNull('village_id');
        } else {
            $query->where('village_id', $villageId);
        }

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        $query->update(['is_pinned' => false]);
    }

    private function normalizeVisibleRoles(array &$data): void
    {
        if (! array_key_exists('visible_roles', $data)) {
            return;
        }

        $data['visible_roles'] = empty($data['visible_roles'])
            ? null
            : array_values($data['visible_roles']);
    }
}
