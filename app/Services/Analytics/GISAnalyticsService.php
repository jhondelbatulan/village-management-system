<?php

namespace App\Services\Analytics;

use App\Models\Household;
use App\Models\Village;

class GISAnalyticsService
{
    public function getSystemStats(): array
    {
        $totalHouseholds = Household::where('status', 'active')
            ->whereNotNull('latitude')->whereNotNull('longitude')->count();
        $villagesWithGIS = Village::where('status', 'active')
            ->whereNotNull('latitude')->whereNotNull('longitude')->count();
        $totalVillages = Village::where('status', 'active')->count();

        return [
            'mapped_households' => $totalHouseholds,
            'villages_mapped' => $villagesWithGIS,
            'total_villages' => $totalVillages,
            'coverage_pct' => $totalVillages > 0 ? round(($villagesWithGIS / $totalVillages) * 100, 1) : 0,
        ];
    }

    public function getVillageStats(int $villageId): array
    {
        $totalHouseholds = Household::where('village_id', $villageId)->where('status', 'active')->count();
        $mappedHouseholds = Household::where('village_id', $villageId)
            ->where('status', 'active')
            ->whereNotNull('latitude')->whereNotNull('longitude')->count();

        return [
            'total_households' => $totalHouseholds,
            'mapped_households' => $mappedHouseholds,
            'coverage_pct' => $totalHouseholds > 0 ? round(($mappedHouseholds / $totalHouseholds) * 100, 1) : 0,
        ];
    }
}
