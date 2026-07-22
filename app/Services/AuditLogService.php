<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditLogService
{
    public static function log(
        string $action,
        ?string $module = null,
        ?string $description = null,
        ?string $recordType = null,
        ?int $recordId = null,
        mixed $oldValues = null,
        mixed $newValues = null,
        ?int $villageId = null,
    ): void {
        $user = auth()->user();

        AuditLog::create([
            'user_id' => $user?->id,
            'role' => $user?->role?->role_slug ?? null,
            'village_id' => $villageId ?? $user?->village_id,
            'action' => $action,
            'module' => $module,
            'record_type' => $recordType,
            'record_id' => $recordId,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'browser' => self::parseBrowser(request()->userAgent()),
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
        ]);
    }

    public static function logModel(
        string $action,
        string $module,
        $model,
        ?string $description = null,
    ): void {
        $user = auth()->user();
        $dirty = $model->getDirty();
        $original = $model->getOriginal();

        $oldValues = [];
        $newValues = [];
        foreach ($dirty as $key => $value) {
            if ($key === 'updated_at' || $key === 'created_at') continue;
            $oldValues[$key] = $original[$key] ?? null;
            $newValues[$key] = $value;
        }

        static::log(
            action: $action,
            module: $module,
            description: $description,
            recordType: class_basename($model),
            recordId: $model->id,
            oldValues: !empty($oldValues) ? $oldValues : null,
            newValues: !empty($newValues) ? $newValues : null,
            villageId: method_exists($model, 'village_id') ? $model->village_id : null,
        );
    }

    public static function parseBrowser(?string $userAgent): ?string
    {
        if (!$userAgent) return null;
        $ua = strtolower($userAgent);
        return match(true) {
            str_contains($ua, 'firefox') => 'Firefox',
            str_contains($ua, 'edg') => 'Edge',
            str_contains($ua, 'chrome') => 'Chrome',
            str_contains($ua, 'safari') => 'Safari',
            str_contains($ua, 'opera') || str_contains($ua, 'opr') => 'Opera',
            default => 'Other',
        };
    }

    public static function getRecent(int $limit = 20)
    {
        return AuditLog::with('user.role', 'village')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public static function getStats(): array
    {
        return [
            'total_today' => AuditLog::whereDate('created_at', today())->count(),
            'total_week' => AuditLog::where('created_at', '>=', now()->subWeek())->count(),
            'total_month' => AuditLog::where('created_at', '>=', now()->subMonth())->count(),
            'total_all' => AuditLog::count(),
            'unique_users_today' => AuditLog::whereDate('created_at', today())->distinct('user_id')->count(),
            'top_actions' => AuditLog::where('created_at', '>=', now()->subWeek())
                ->selectRaw('action, COUNT(*) as count')
                ->groupBy('action')
                ->orderByDesc('count')
                ->limit(5)
                ->pluck('count', 'action')
                ->toArray(),
        ];
    }
}
