<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    protected $fillable = [
        'user_id',
        'theme',
        'accent_color',
        'font_size',
        'sidebar_style',
        'dashboard_layout',
        'dashboard_widgets',
        'notification_sound',
        'browser_notifications',
        'animation_level',
        'compact_mode',
        'language',
        'display_name',
    ];

    protected $casts = [
        'dashboard_widgets' => 'array',
        'notification_sound' => 'boolean',
        'browser_notifications' => 'boolean',
        'compact_mode' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function forUser(int $userId): static
    {
        return static::firstOrCreate(['user_id' => $userId]);
    }

    public static function defaults(): array
    {
        return [
            'theme' => 'light',
            'accent_color' => 'blue',
            'font_size' => 'medium',
            'sidebar_style' => 'expanded',
            'dashboard_layout' => 'default',
            'dashboard_widgets' => null,
            'notification_sound' => true,
            'browser_notifications' => false,
            'animation_level' => 'full',
            'compact_mode' => false,
            'language' => 'en',
            'display_name' => null,
        ];
    }

    public function getDefaultDashboardWidgets(): array
    {
        return [
            'population_summary' => true,
            'household_summary' => true,
            'resident_summary' => true,
            'payment_summary' => true,
            'analytics_summary' => true,
            'participation_summary' => true,
            'announcements' => true,
            'calendar' => true,
            'system_notices' => true,
        ];
    }
}
