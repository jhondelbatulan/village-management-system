<?php

namespace App\Services\Analytics;

use App\Models\Household;
use App\Models\Resident;
use App\Models\Village;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class HouseholdStabilityService
{
    public function __construct(
        protected StatisticsService $stats
    ) {}

    public function getSummaryStats(?int $villageId = null): array
    {
        $households = Household::where('status', 'active')
            ->when($villageId, fn($q) => $q->where('village_id', $villageId))
            ->withCount('residents')
            ->get();

        $memberCounts = $households->pluck('residents_count')->values()->toArray();
        $totalHouseholds = count($households);
        $totalResidents = array_sum($memberCounts);
        $avgSize = $totalHouseholds > 0 ? round($totalResidents / $totalHouseholds, 2) : 0;
        $maxSize = !empty($memberCounts) ? max($memberCounts) : 0;
        $minSize = !empty($memberCounts) ? min($memberCounts) : 0;

        $largestHousehold = $households->where('residents_count', $maxSize)->first();
        $smallestHousehold = $households->where('residents_count', $minSize)->first();

        $ageStdDev = $this->calculateAgeStdDev($villageId);
        $stabilityIndex = $this->calculateStabilityIndex($villageId);

        return [
            'total_households' => $totalHouseholds,
            'total_members' => $totalResidents,
            'avg_members' => $avgSize,
            'max_members' => $maxSize,
            'min_members' => $minSize,
            'largest_household' => $largestHousehold ? [
                'id' => $largestHousehold->id,
                'number' => $largestHousehold->household_number,
                'members' => $maxSize,
            ] : null,
            'smallest_household' => $smallestHousehold ? [
                'id' => $smallestHousehold->id,
                'number' => $smallestHousehold->household_number,
                'members' => $minSize,
            ] : null,
            'stability_index' => $stabilityIndex,
            'size_distribution' => $this->getSizeDistribution($villageId),
        ];
    }

    public function getSizeDistribution(?int $villageId = null): array
    {
        $counts = Household::where('status', 'active')
            ->when($villageId, fn($q) => $q->where('village_id', $villageId))
            ->withCount('residents')
            ->pluck('residents_count')
            ->toArray();

        $brackets = [
            '1 member' => 0,
            '2 members' => 0,
            '3-4 members' => 0,
            '5-6 members' => 0,
            '7-8 members' => 0,
            '9+ members' => 0,
        ];

        foreach ($counts as $c) {
            if ($c <= 1) $brackets['1 member']++;
            elseif ($c <= 2) $brackets['2 members']++;
            elseif ($c <= 4) $brackets['3-4 members']++;
            elseif ($c <= 6) $brackets['5-6 members']++;
            elseif ($c <= 8) $brackets['7-8 members']++;
            else $brackets['9+ members']++;
        }

        return $this->stats->frequencyCount($brackets);
    }

    protected function calculateStabilityIndex(?int $villageId = null): float
    {
        $residents = Resident::where('status', 'active')
            ->when($villageId, fn($q) => $q->where('village_id', $villageId))
            ->whereNotNull('years_of_residency')
            ->pluck('years_of_residency')
            ->toArray();

        if (empty($residents)) return 0;

        $avgYears = array_sum($residents) / count($residents);
        $maxYears = max($residents);
        $minYears = min($residents);
        $range = $maxYears - $minYears;

        $avgHhSize = $this->getAverageHhSize($villageId);
        $hhCount = Household::where('status', 'active')
            ->when($villageId, fn($q) => $q->where('village_id', $villageId))
            ->count();

        $sizeScore = $hhCount > 0 ? min(($avgHhSize / 5) * 50, 50) : 0;
        $durationScore = min(($avgYears / 20) * 50, 50);

        return round($sizeScore + $durationScore, 2);
    }

    protected function getAverageHhSize(?int $villageId = null): float
    {
        $households = Household::where('status', 'active')
            ->when($villageId, fn($q) => $q->where('village_id', $villageId))
            ->withCount('residents')
            ->get();

        if ($households->isEmpty()) return 0;

        return $households->avg('residents_count') ?: 0;
    }

    protected function calculateAgeStdDev(?int $villageId = null): float
    {
        $ages = Resident::where('status', 'active')
            ->when($villageId, fn($q) => $q->where('village_id', $villageId))
            ->whereNotNull('age')
            ->pluck('age')
            ->toArray();

        if (count($ages) < 2) return 0;

        $mean = array_sum($ages) / count($ages);
        $variance = array_reduce($ages, fn($carry, $age) => $carry + pow($age - $mean, 2), 0) / count($ages);

        return round(sqrt($variance), 2);
    }

    public function getVillageComparison(): array
    {
        $villages = Village::where('status', 'active')->get();
        $results = [];

        foreach ($villages as $village) {
            $summary = $this->getSummaryStats($village->id);
            $results[] = [
                'village_id' => $village->id,
                'village_name' => $village->village_name,
                'total_households' => $summary['total_households'],
                'avg_members' => $summary['avg_members'],
                'stability_index' => $summary['stability_index'],
            ];
        }

        return collect($results)->sortByDesc('stability_index')->values()->toArray();
    }

    public function getInsights(?int $villageId = null): array
    {
        $insights = [];
        $summary = $this->getSummaryStats($villageId);

        if ($summary['avg_members'] > 6) {
            $insights[] = [
                'title' => 'Large Average Household',
                'description' => "Average household size of {$summary['avg_members']} members is above typical, indicating extended family structures.",
                'priority' => 'info',
                'icon' => 'people',
            ];
        }

        if ($summary['stability_index'] > 60) {
            $insights[] = [
                'title' => 'High Household Stability',
                'description' => "Stability index of {$summary['stability_index']} indicates strong household stability across the community.",
                'priority' => 'success',
                'icon' => 'house-heart',
            ];
        }

        return $insights;
    }
}
