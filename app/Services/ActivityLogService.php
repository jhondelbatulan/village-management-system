<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogService
{
    public static function log(
        string $action,
        ?string $module = null,
        ?string $description = null,
        $subject = null,
        array $properties = [],
        ?int $villageId = null,
    ): void {
        $user = auth()->user();

        ActivityLog::create([
            'user_id' => $user?->id,
            'role' => $user?->role?->role_slug ?? null,
            'village_id' => $villageId ?? $user?->village_id,
            'action' => $action,
            'module' => $module,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id ?? null,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => request()->ip() ?? null,
            'user_agent' => request()->userAgent() ?? null,
            'browser' => class_exists(AuditLogService::class) ? AuditLogService::parseBrowser(request()->userAgent()) : null,
        ]);
    }

    public static function getFiltered(array $filters = [], int $perPage = 25)
    {
        $query = ActivityLog::with('user.role', 'village');

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }
        if (!empty($filters['village_id'])) {
            $query->where('village_id', $filters['village_id']);
        }
        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }
        if (!empty($filters['module'])) {
            $query->where('module', $filters['module']);
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate($perPage);
    }

    public static function getStats(): array
    {
        return [
            'total_today' => ActivityLog::whereDate('created_at', today())->count(),
            'total_week' => ActivityLog::where('created_at', '>=', now()->subWeek())->count(),
            'total_month' => ActivityLog::where('created_at', '>=', now()->subMonth())->count(),
            'unique_users_today' => ActivityLog::whereDate('created_at', today())->distinct('user_id')->count(),
            'top_modules' => ActivityLog::where('created_at', '>=', now()->subWeek())
                ->selectRaw('module, COUNT(*) as count')
                ->whereNotNull('module')
                ->groupBy('module')
                ->orderByDesc('count')
                ->limit(5)
                ->pluck('count', 'module')
                ->toArray(),
        ];
    }

    public static function getDistinctActions(): \Illuminate\Support\Collection
    {
        return ActivityLog::whereNotNull('action')
            ->distinct()
            ->pluck('action')
            ->sort()
            ->values();
    }
}
