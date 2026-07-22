<?php

namespace App\Services\Analytics;

use App\Models\AuditLog;

class ActivityService
{
    public function getRecentActivity(?int $villageId = null, int $limit = 15): \Illuminate\Support\Collection
    {
        $query = AuditLog::with('user');

        if ($villageId) {
            $query->whereHas('user', fn($q) => $q->where('village_id', $villageId));
        }

        return $query->latest()
            ->take($limit)
            ->get()
            ->map(fn($log) => [
                'id' => $log->id,
                'action' => $log->action,
                'module' => $log->module,
                'description' => $log->description,
                'user' => $log->user?->full_name ?? 'System',
                'user_role' => $log->user?->role?->role_name ?? '',
                'village' => $log->user?->village?->village_name ?? 'N/A',
                'created_at' => $log->created_at,
                'time_ago' => $log->created_at->diffForHumans(),
                'icon' => $this->getActivityIcon($log->action),
                'color' => $this->getActivityColor($log->action),
            ]);
    }

    public function getSystemRecentActivity(int $limit = 20): \Illuminate\Support\Collection
    {
        return AuditLog::with('user')
            ->latest()
            ->take($limit)
            ->get()
            ->map(fn($log) => [
                'id' => $log->id,
                'action' => $log->action,
                'module' => $log->module,
                'description' => $log->description,
                'user' => $log->user?->full_name ?? 'System',
                'village' => $log->user?->village?->village_name ?? 'N/A',
                'created_at' => $log->created_at,
                'time_ago' => $log->created_at->diffForHumans(),
                'icon' => $this->getActivityIcon($log->action),
                'color' => $this->getActivityColor($log->action),
            ]);
    }

    private function getActivityIcon(string $action): string
    {
        return match(true) {
            str_contains($action, 'Resident') => 'bi-person-plus',
            str_contains($action, 'Household') => 'bi-house-door',
            str_contains($action, 'Payment') || str_contains($action, 'Collect') => 'bi-cash-stack',
            str_contains($action, 'Announcement') => 'bi-megaphone',
            str_contains($action, 'Event') => 'bi-calendar-event',
            str_contains($action, 'Approval') || str_contains($action, 'Approve') => 'bi-check-circle',
            str_contains($action, 'Reject') => 'bi-x-circle',
            str_contains($action, 'GIS') || str_contains($action, 'Map') => 'bi-geo-alt',
            str_contains($action, 'Attendance') || str_contains($action, 'Participation') => 'bi-people',
            str_contains($action, 'User') => 'bi-person-gear',
            str_contains($action, 'Village') => 'bi-building',
            default => 'bi-activity',
        };
    }

    private function getActivityColor(string $action): string
    {
        return match(true) {
            str_contains($action, 'Create') || str_contains($action, 'Register') => 'success',
            str_contains($action, 'Approve') => 'primary',
            str_contains($action, 'Reject') || str_contains($action, 'Delete') => 'danger',
            str_contains($action, 'Update') || str_contains($action, 'Edit') => 'info',
            str_contains($action, 'Payment') || str_contains($action, 'Collect') => 'success',
            str_contains($action, 'Submit') => 'warning',
            default => 'secondary',
        };
    }
}
