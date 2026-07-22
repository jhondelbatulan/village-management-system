<?php

namespace App\Helpers;

use App\Models\SystemSetting;

/**
 * System-wide helper utilities for branding and role display.
 */
class SystemHelper
{
    /**
     * Get the human-readable label for a role key.
     */
    public static function roleLabel(?string $role): string
    {
        if (! $role) {
            return 'User';
        }

        return config("app_settings.roles.{$role}", ucfirst(str_replace('_', ' ', $role)));
    }

    /**
     * Get the system name from database, falling back to config.
     */
    public static function systemName(): string
    {
        $dbValue = SystemSetting::getValue('system_name');
        return $dbValue ?? config('app_settings.name', 'Multi-Village Management System');
    }

    /**
     * Get asset URL for a configured system asset.
     */
    public static function asset(string $key): string
    {
        $path = config("app_settings.assets.{$key}", '');

        return $path ? asset($path) : '';
    }
}
