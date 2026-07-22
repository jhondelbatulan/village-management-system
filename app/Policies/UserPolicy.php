<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role?->role_slug, ['super_admin', 'admin']);
    }

    public function view(User $currentUser, User $model): bool
    {
        if ($currentUser->role?->role_slug === 'super_admin') {
            return true;
        }

        if ($currentUser->role?->role_slug === 'admin') {
            return $currentUser->village_id === $model->village_id;
        }

        return $currentUser->id === $model->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role?->role_slug, ['super_admin', 'admin']);
    }

    public function update(User $currentUser, User $model): bool
    {
        if ($currentUser->role?->role_slug === 'super_admin') {
            return true;
        }

        if ($currentUser->role?->role_slug === 'admin') {
            return $currentUser->village_id === $model->village_id
                && $model->role?->role_slug !== 'super_admin';
        }

        return $currentUser->id === $model->id;
    }

    public function delete(User $currentUser, User $model): bool
    {
        if ($currentUser->id === $model->id) {
            return false;
        }

        if ($currentUser->role?->role_slug === 'super_admin') {
            return $model->role?->role_slug !== 'super_admin';
        }

        if ($currentUser->role?->role_slug === 'admin') {
            return $currentUser->village_id === $model->village_id
                && in_array($model->role?->role_slug, ['secretary', 'treasurer', 'resident']);
        }

        return false;
    }
}
