<?php

namespace App\Support;

class RoleRedirect
{
    /**
     * Route name of the dashboard that belongs to the given role slug.
     * Returns null when the account has no (recognizable) role, so callers
     * can break out instead of bouncing the user between login and "/" forever.
     */
    public static function routeName(?string $roleSlug): ?string
    {
        return match ($roleSlug) {
            'super_admin' => 'dashboard.super_admin',
            'admin' => 'dashboard.admin',
            'secretary' => 'dashboard.secretary',
            'treasurer' => 'dashboard.treasurer',
            'resident' => 'dashboard.resident',
            default => null,
        };
    }
}
