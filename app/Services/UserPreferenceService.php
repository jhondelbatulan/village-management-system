<?php

namespace App\Services;

use App\Models\UserPreference;

class UserPreferenceService
{
    public function getForUser(int $userId): UserPreference
    {
        return UserPreference::forUser($userId);
    }

    public function updateForUser(int $userId, array $data): UserPreference
    {
        $prefs = UserPreference::forUser($userId);
        $prefs->update($data);
        return $prefs->fresh();
    }

    public function getTheme(int $userId): string
    {
        return $this->getForUser($userId)->theme;
    }

    public function getAccentColor(int $userId): string
    {
        return $this->getForUser($userId)->accent_color;
    }

    public function getFontSize(int $userId): string
    {
        return $this->getForUser($userId)->font_size;
    }

    public function getSidebarStyle(int $userId): string
    {
        return $this->getForUser($userId)->sidebar_style;
    }

    public function getAnimationLevel(int $userId): string
    {
        return $this->getForUser($userId)->animation_level;
    }

    public function getDashboardWidgets(int $userId): array
    {
        $prefs = $this->getForUser($userId);
        return $prefs->dashboard_widgets ?? $prefs->getDefaultDashboardWidgets();
    }

    public function getCompactMode(int $userId): bool
    {
        return $this->getForUser($userId)->compact_mode;
    }
}
