<?php

namespace App\Http\Traits;

use App\Models\User;

trait VillageAccessAware
{
    protected function getVillageId(?User $user): ?int
    {
        if (!$user) return null;
        if (($user->role?->role_slug ?? '') === 'super_admin') return null;
        return $user->village_id;
    }

    protected function enforceVillageAccess(?User $user, int $villageId): void
    {
        if (!$user) return;
        if (($user->role?->role_slug ?? '') === 'super_admin') return;
        if ($user->village_id && $user->village_id != $villageId) {
            abort(403, 'You do not have access to this village\'s analytics.');
        }
    }
}
