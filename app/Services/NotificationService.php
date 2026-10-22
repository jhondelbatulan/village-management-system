<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class NotificationService
{
    public function create(
        int|User $user,
        string $type,
        string $title,
        ?string $message = null,
        ?string $icon = null,
        ?string $color = null,
        ?string $actionUrl = null,
    ): Notification {
        $userId = $user instanceof User ? $user->id : $user;

        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'icon' => $icon,
            'color' => $color,
            'action_url' => $actionUrl,
        ]);
    }

    public function getUnreadForUser(int $userId, int $limit = 10): Collection
    {
        return Notification::forUser($userId)
            ->unread()
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getRecentForUser(int $userId, int $limit = 20): Collection
    {
        return Notification::forUser($userId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getUnreadCount(int $userId): int
    {
        return Notification::forUser($userId)
            ->unread()
            ->count();
    }

    public function markAsRead(int $notificationId, int $userId): bool
    {
        return Notification::forUser($userId)
            ->where('id', $notificationId)
            ->update(['read_at' => now()]) > 0;
    }

    public function markAllAsRead(int $userId): int
    {
        return Notification::forUser($userId)
            ->unread()
            ->update(['read_at' => now()]);
    }

    public function deleteForUser(int $notificationId, int $userId): bool
    {
        return Notification::forUser($userId)
            ->where('id', $notificationId)
            ->delete() > 0;
    }

    public function createFromPlaceholder(
        string $type,
        string $title,
        ?string $message = null,
        ?string $actionUrl = null,
        ?int $userId = null,
    ): ?Notification {
        $userId = $userId ?? auth()->id();
        if (! $userId) {
            return null;
        }

        $icons = [
            'approval' => 'bi-check2-circle',
            'billing' => 'bi-receipt',
            'payment' => 'bi-cash-coin',
            'announcement' => 'bi-megaphone',
            'event' => 'bi-calendar-event',
            'system' => 'bi-gear',
            'info' => 'bi-info-circle',
            'warning' => 'bi-exclamation-triangle',
        ];

        $colors = [
            'approval' => 'primary',
            'billing' => 'success',
            'payment' => 'info',
            'announcement' => 'warning',
            'event' => 'primary',
            'system' => 'secondary',
            'info' => 'info',
            'warning' => 'warning',
        ];

        return $this->create(
            $userId,
            $type,
            $title,
            $message,
            $icons[$type] ?? 'bi-bell',
            $colors[$type] ?? 'secondary',
            $actionUrl,
        );
    }

    public function notifyAdmins(
        ?int $villageId,
        string $title,
        string $message,
        ?string $actionUrl = null,
        string $type = 'approval',
    ): void {
        $admins = User::query()
            ->where('status', 'active')
            ->whereHas('role', fn ($q) => $q->where('role_slug', 'admin'))
            ->when($villageId, fn ($q) => $q->where('village_id', $villageId))
            ->get();

        if ($admins->isEmpty()) {
            $this->notifySuperAdmins($title, $message, $actionUrl, $type);

            return;
        }

        foreach ($admins as $admin) {
            $this->createFromPlaceholder($type, $title, $message, $actionUrl, $admin->id);
        }
    }

    public function notifySuperAdmins(
        string $title,
        string $message,
        ?string $actionUrl = null,
        string $type = 'approval',
    ): void {
        $superAdmins = User::query()
            ->where('status', 'active')
            ->whereHas('role', fn ($q) => $q->where('role_slug', 'super_admin'))
            ->get();

        foreach ($superAdmins as $user) {
            $this->createFromPlaceholder($type, $title, $message, $actionUrl, $user->id);
        }
    }

    /**
     * Notify active staff accounts (admin, secretary, treasurer) of a village,
     * excluding the user who performed the action. Pass a null village to
     * notify the staff of every village.
     */
    public function notifyVillageStaff(
        ?int $villageId,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?int $exceptUserId = null,
    ): int {
        $userIds = User::query()
            ->where('status', 'active')
            ->whereHas('role', fn ($q) => $q->whereIn('role_slug', ['admin', 'secretary', 'treasurer']))
            ->when($villageId, fn ($q) => $q->where('village_id', $villageId))
            ->when($exceptUserId, fn ($q) => $q->where('id', '!=', $exceptUserId))
            ->pluck('id');

        return $this->notifyUserIds($userIds, $type, $title, $message, $actionUrl);
    }

    public function notifyUser(
        ?int $userId,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
    ): void {
        if ($userId) {
            $this->createFromPlaceholder($type, $title, $message, $actionUrl, $userId);
        }
    }

    /**
     * Notify every active resident account in the given village(s).
     * Pass null to notify residents of all villages, an empty array to notify nobody.
     */
    public function notifyResidents(
        int|array|null $villageIds,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
    ): int {
        if (is_array($villageIds) && empty($villageIds)) {
            return 0;
        }

        $userIds = User::query()
            ->where('status', 'active')
            ->whereHas('role', fn ($q) => $q->where('role_slug', 'resident'))
            ->when($villageIds !== null, fn ($q) => $q->whereIn('village_id', (array) $villageIds))
            ->pluck('id');

        return $this->notifyUserIds($userIds, $type, $title, $message, $actionUrl);
    }

    /**
     * Notify the resident accounts belonging to the given households.
     */
    public function notifyHouseholdResidents(
        array $householdIds,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
    ): int {
        if (empty($householdIds)) {
            return 0;
        }

        $userIds = User::query()
            ->where('status', 'active')
            ->whereIn('household_id', $householdIds)
            ->whereHas('role', fn ($q) => $q->where('role_slug', 'resident'))
            ->pluck('id');

        return $this->notifyUserIds($userIds, $type, $title, $message, $actionUrl);
    }

    /**
     * Notify the resident accounts the announcement is visible to
     * (village, household and audience rules all apply).
     */
    public function notifyAnnouncementResidents(Announcement $announcement): int
    {
        if (! $announcement->isVisibleToRole('resident')) {
            return 0;
        }

        $recipients = User::query()
            ->where('status', 'active')
            ->whereHas('role', fn ($q) => $q->where('role_slug', 'resident'))
            ->get()
            ->filter(fn (User $user) => $announcement->isVisibleToResident(
                (int) $user->village_id,
                $user->household_id
            ));

        foreach ($recipients as $user) {
            $this->createFromPlaceholder(
                'announcement',
                'New Announcement',
                "New announcement: {$announcement->title}",
                route('announcements.show', $announcement),
                $user->id
            );
        }

        return $recipients->count();
    }

    private function notifyUserIds(
        $userIds,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
    ): int {
        foreach ($userIds as $userId) {
            $this->createFromPlaceholder($type, $title, $message, $actionUrl, $userId);
        }

        return count($userIds);
    }
}
