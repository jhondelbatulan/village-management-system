<?php

namespace App\Services\Analytics;

use App\Models\Household;
use App\Models\Resident;
use App\Models\Village;
use Illuminate\Database\Eloquent\Builder;

class PopulationDensityService
{
    public function __construct(
        protected StatisticsService $stats
    ) {}

    protected function baseQuery(?int $villageId = null): Builder
    {
        $query = Resident::where('status', 'active');
        if ($villageId) {
            $query->where('village_id', $villageId);
        }
        return $query;
    }

    public function getSummaryStats(?int $villageId = null): array
    {
        $village = $villageId ? Village::find($villageId) : null;
        $population = $this->baseQuery($villageId)->count();
        $households = Household::when($villageId, fn($q) => $q->where('village_id', $villageId))
            ->where('status', 'active')->count();

        $landArea = $village?->land_area_sq_km ?? 0;
        $popDensity = $landArea > 0 ? round($population / $landArea, 2) : 0;
        $hhDensity = $landArea > 0 ? round($households / $landArea, 2) : 0;
        $avgHhSize = $households > 0 ? round($population / $households, 2) : 0;

        return [
            'population' => $population,
            'households' => $households,
            'land_area_sq_km' => $landArea,
            'population_density' => $popDensity,
            'household_density' => $hhDensity,
            'avg_household_size' => $avgHhSize,
        ];
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
                'population' => $summary['population'],
                'households' => $summary['households'],
                'land_area_sq_km' => $summary['land_area_sq_km'],
                'population_density' => $summary['population_density'],
                'household_density' => $summary['household_density'],
                'avg_household_size' => $summary['avg_household_size'],
            ];
        }

        return collect($results)->sortByDesc('population_density')->values()->toArray();
    }

    public function getVillageRankings(): array
    {
        $comparison = $this->getVillageComparison();
        $rank = 1;
        foreach ($comparison as &$item) {
            $item['rank'] = $rank++;
        }
        return $comparison;
    }

    public function getHeatMapData(): array
    {
        return Village::where('status', 'active')
            ->get()
            ->map(function ($village) {
                $summary = $this->getSummaryStats($village->id);
                return [
                    'village_id' => $village->id,
                    'village_name' => $village->village_name,
                    'latitude' => $village->latitude,
                    'longitude' => $village->longitude,
                    'population_density' => $summary['population_density'],
                    'population' => $summary['population'],
                    'land_area_sq_km' => $summary['land_area_sq_km'],
                ];
            })
            ->toArray();
    }

    public function getInsights(?int $villageId = null): array
    {
        $insights = [];

        if (!$villageId) {
            $comparison = $this->getVillageComparison();
            if (!empty($comparison)) {
                $highest = collect($comparison)->first();
                $lowest = collect($comparison)->last();
                $insights[] = [
                    'title' => 'Highest Density',
                    'description' => "{$highest['village_name']} has the highest population density at {$highest['population_density']} people/km².",
                    'priority' => 'warning',
                    'icon' => 'arrow-up',
                ];
                $insights[] = [
                    'title' => 'Lowest Density',
                    'description' => "{$lowest['village_name']} has the lowest population density at {$lowest['population_density']} people/km².",
                    'priority' => 'info',
                    'icon' => 'arrow-down',
                ];
            }
        }

        $summary = $this->getSummaryStats($villageId);
        if ($summary['land_area_sq_km'] > 0) {
            if ($summary['population_density'] > 5000) {
                $insights[] = [
                    'title' => 'High Density Area',
                    'description' => "Population density of {$summary['population_density']} people/km² indicates a highly urbanized area.",
                    'priority' => 'danger',
                    'icon' => 'building',
                ];
            } elseif ($summary['population_density'] > 1000) {
                $insights[] = [
                    'title' => 'Moderate Density',
                    'description' => "Population density of {$summary['population_density']} people/km² indicates moderate density.",
                    'priority' => 'info',
                    'icon' => 'house',
                ];
            }
        } else {
            $insights[] = [
                'title' => 'No Land Area Set',
                'description' => 'Set the village land area in Village Profile to enable density calculations.',
                'priority' => 'secondary',
                'icon' => 'info-circle',
            ];
        }

        return $insights;
    }
}
