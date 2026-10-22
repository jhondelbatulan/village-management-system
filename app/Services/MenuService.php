<?php

namespace App\Services;

use App\Models\User;

class MenuService
{
    public function getMenuForRole(string $role, ?User $user = null): array
    {
        $items = config("menu.items.{$role}", []);

        if ($user) {
            if (in_array($role, ['secretary', 'treasurer'])) {
                $items = $this->resolveVillageRoutes($items, $user->village_id);
            }

            if ($role === 'resident') {
                $items = $this->resolveResidentRoutes($items, $user);
                $items = $this->resolveVillageRoutes($items, $user->household?->village_id);
            }
        }

        return $items;
    }

    public function isActive(array $item, ?string $currentRoute = null): bool
    {
        $currentRoute ??= request()->route()?->getName();

        if (! empty($item['route']) && $item['route'] !== '#' && $item['route'] === $currentRoute) {
            return true;
        }

        if (! empty($item['children'])) {
            foreach ($item['children'] as $child) {
                if ($this->isActive($child, $currentRoute)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function resolveVillageRoutes(array $items, ?int $villageId): array
    {
        if (! $villageId) {
            return $items;
        }

        foreach ($items as &$item) {
            if (isset($item['route']) && $item['route'] === 'villages.show__VILLAGE_ID__') {
                $item['route'] = 'villages.show';
                $item['route_params'] = ['village' => $villageId];
            }
            if (isset($item['route']) && $item['route'] === 'villages.officers.index__VILLAGE_ID__') {
                $item['route'] = 'villages.officers.index';
                $item['route_params'] = ['village' => $villageId];
            }
            if (isset($item['route']) && $item['route'] === 'villages.documents.index__VILLAGE_ID__') {
                $item['route'] = 'villages.documents.index';
                $item['route_params'] = ['village' => $villageId];
            }
            if (isset($item['route']) && $item['route'] === 'payment-config.edit__VILLAGE_ID__') {
                $item['route'] = 'payment-config.edit';
                $item['route_params'] = ['village' => $villageId];
            }
        }
        unset($item);

        return $items;
    }

    private function resolveResidentRoutes(array $items, User $user): array
    {
        $householdId = $user->household_id;

        foreach ($items as &$item) {
            if (isset($item['route']) && $item['route'] === 'household-payments.show__HOUSEHOLD_ID__' && $householdId) {
                $item['route'] = 'household-payments.show';
                $item['route_params'] = ['household' => $householdId];
            }
        }
        unset($item);

        return $items;
    }
}
