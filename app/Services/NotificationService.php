<?php

namespace App\Services;

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
        if (!$userId) return null;

        $icons = [
            'approval' => 'bi-check2-circle',
            'billing' => 'bi-receipt',
            'payment' => 'bi-cash-coin',
            'announcement' => 'bi-megaphone',
            'system' => 'bi-gear',
            'info' => 'bi-info-circle',
            'warning' => 'bi-exclamation-triangle',
        ];

        $colors = [
            'approval' => 'primary',
            'billing' => 'success',
            'payment' => 'info',
            'announcement' => 'warning',
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
}
