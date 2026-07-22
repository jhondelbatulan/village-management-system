<?php

namespace App\Services;

use App\Models\SystemSetting;

class SystemSettingService
{
    public static function get(string $key, mixed $default = null): mixed
    {
        return SystemSetting::getValue($key, $default);
    }

    public static function set(string $key, mixed $value): void
    {
        $old = SystemSetting::getValue($key);
        SystemSetting::setValue($key, $value);

        AuditLogService::log(
            action: 'Settings Modified',
            module: 'System Administration',
            description: "Setting changed: {$key}",
            oldValues: ['value' => $old],
            newValues: ['value' => $value],
        );
    }

    public static function getGrouped(): \Illuminate\Support\Collection
    {
        return SystemSetting::getAllGrouped();
    }

    public static function initDefaults(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('system_settings')) {
            return;
        }

        $defaults = [
            ['group' => 'general', 'key' => 'system_name', 'value' => config('app_settings.name', 'Multi-Village Management System'), 'type' => 'text', 'label' => 'System Name'],
            ['group' => 'general', 'key' => 'system_tagline', 'value' => 'GIS-Enabled Village Management', 'type' => 'text', 'label' => 'System Tagline'],
            ['group' => 'general', 'key' => 'default_timezone', 'value' => 'Asia/Manila', 'type' => 'select', 'label' => 'Default Timezone', 'options' => ['Asia/Manila', 'UTC', 'America/New_York', 'Europe/London']],
            ['group' => 'general', 'key' => 'default_pagination', 'value' => '15', 'type' => 'number', 'label' => 'Default Pagination'],
            ['group' => 'general', 'key' => 'maintenance_mode', 'value' => '0', 'type' => 'toggle', 'label' => 'Maintenance Mode'],
            ['group' => 'general', 'key' => 'system_logo', 'value' => '', 'type' => 'text', 'label' => 'System Logo'],
            ['group' => 'general', 'key' => 'system_favicon', 'value' => '', 'type' => 'text', 'label' => 'System Favicon'],

            ['group' => 'security', 'key' => 'password_min_length', 'value' => '8', 'type' => 'number', 'label' => 'Minimum Password Length'],
            ['group' => 'security', 'key' => 'password_require_uppercase', 'value' => '1', 'type' => 'toggle', 'label' => 'Require Uppercase Letter'],
            ['group' => 'security', 'key' => 'password_require_lowercase', 'value' => '1', 'type' => 'toggle', 'label' => 'Require Lowercase Letter'],
            ['group' => 'security', 'key' => 'password_require_number', 'value' => '1', 'type' => 'toggle', 'label' => 'Require Number'],
            ['group' => 'security', 'key' => 'password_require_special', 'value' => '0', 'type' => 'toggle', 'label' => 'Require Special Character'],
            ['group' => 'security', 'key' => 'password_history_limit', 'value' => '5', 'type' => 'number', 'label' => 'Password History Limit'],
            ['group' => 'security', 'key' => 'account_lockout_attempts', 'value' => '5', 'type' => 'number', 'label' => 'Lockout After Failed Attempts'],
            ['group' => 'security', 'key' => 'account_lockout_duration', 'value' => '30', 'type' => 'number', 'label' => 'Lockout Duration (minutes)'],
            ['group' => 'security', 'key' => 'session_lifetime', 'value' => '120', 'type' => 'number', 'label' => 'Session Lifetime (minutes)'],

            ['group' => 'analytics', 'key' => 'analytics_refresh_interval', 'value' => '60', 'type' => 'number', 'label' => 'Analytics Refresh Interval (minutes)'],
            ['group' => 'analytics', 'key' => 'enable_predictive_analytics', 'value' => '1', 'type' => 'toggle', 'label' => 'Enable Predictive Analytics'],
        ];

        foreach ($defaults as $setting) {
            SystemSetting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    public static function getSecuritySettings(): array
    {
        return SystemSetting::getGroup('security')->pluck('value', 'key')->toArray();
    }
}
