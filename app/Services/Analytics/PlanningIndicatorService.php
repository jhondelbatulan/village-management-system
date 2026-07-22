<?php

namespace App\Services\Analytics;

use App\Models\Village;

class PlanningIndicatorService
{
    public function __construct(
        protected DependencyRatioService $dependency,
        protected PopulationDensityService $density,
        protected GrowthAnalyticsService $growth,
        protected ResidencyDurationAnalyticsService $residency,
        protected MigrationAnalyticsService $migration,
        protected HouseholdStabilityService $householdStability,
        protected CommunityStructureService $communityStructure,
        protected StatisticsService $stats
    ) {}

    public function getAllIndicators(?int $villageId = null): array
    {
        return [
            'dependency' => $this->dependency->getSummaryStats($villageId),
            'density' => $this->density->getSummaryStats($villageId),
            'growth' => $this->growth->getSummaryStats($villageId),
            'residency' => $this->residency->getSummaryStats($villageId),
            'migration' => $this->migration->getSummaryStats($villageId),
            'household_stability' => $this->householdStability->getSummaryStats($villageId),
            'community_structure' => $this->communityStructure->getSummaryStats($villageId),
        ];
    }

    public function getDemographicComparison(): array
    {
        $villages = Village::where('status', 'active')->get();
        $results = [];

        foreach ($villages as $village) {
            $dep = $this->dependency->getSummaryStats($village->id);
            $den = $this->density->getSummaryStats($village->id);
            $gro = $this->growth->getSummaryStats($village->id);
            $res = $this->residency->getSummaryStats($village->id);
            $hh = $this->householdStability->getSummaryStats($village->id);
            $com = $this->communityStructure->getSummaryStats($village->id);

            $avgAge = $this->getAverageAge($village->id);

            $results[] = [
                'village_id' => $village->id,
                'village_name' => $village->village_name,
                'population' => $den['population'],
                'population_density' => $den['population_density'],
                'annual_growth' => $gro['annual_growth'],
                'average_age' => $avgAge,
                'dependency_ratio' => $dep['total_dependency_ratio'],
                'household_density' => $den['household_density'],
                'avg_residency_years' => $res['average_years'],
                'avg_household_size' => $hh['avg_members'],
                'stability_index' => $hh['stability_index'],
                'working_pct' => $com['working_pct'],
            ];
        }

        return $results;
    }

    public function getDemographicRankings(): array
    {
        $comparison = $this->getDemographicComparison();
        $count = count($comparison);

        $rankings = [
            'population' => collect($comparison)->sortByDesc('population')->values()->map(fn($item, $i) => [...$item, 'rank' => $i + 1])->toArray(),
            'density' => collect($comparison)->sortByDesc('population_density')->values()->map(fn($item, $i) => [...$item, 'rank' => $i + 1])->toArray(),
            'growth' => collect($comparison)->sortByDesc('annual_growth')->values()->map(fn($item, $i) => [...$item, 'rank' => $i + 1])->toArray(),
            'avg_age' => collect($comparison)->sortBy('average_age')->values()->map(fn($item, $i) => [...$item, 'rank' => $i + 1])->toArray(),
            'dependency' => collect($comparison)->sortBy('dependency_ratio')->values()->map(fn($item, $i) => [...$item, 'rank' => $i + 1])->toArray(),
            'household_density' => collect($comparison)->sortByDesc('household_density')->values()->map(fn($item, $i) => [...$item, 'rank' => $i + 1])->toArray(),
            'residency' => collect($comparison)->sortByDesc('avg_residency_years')->values()->map(fn($item, $i) => [...$item, 'rank' => $i + 1])->toArray(),
            'stability' => collect($comparison)->sortByDesc('stability_index')->values()->map(fn($item, $i) => [...$item, 'rank' => $i + 1])->toArray(),
        ];

        return $rankings;
    }

    public function search(string $query, ?int $villageId = null): array
    {
        $results = [];
        $q = strtolower($query);

        if (in_array($q, ['dependency', 'dependency ratio', 'dependent'])) {
            $results['dependency'] = $this->dependency->getSummaryStats($villageId);
        }
        if (in_array($q, ['density', 'population density', 'land area'])) {
            $results['density'] = $this->density->getSummaryStats($villageId);
        }
        if (in_array($q, ['growth', 'population growth', 'growth rate'])) {
            $results['growth'] = $this->growth->getSummaryStats($villageId);
        }
        if (in_array($q, ['residency', 'residency duration', 'years'])) {
            $results['residency'] = $this->residency->getSummaryStats($villageId);
        }
        if (in_array($q, ['migration', 'new residents', 'transferred'])) {
            $results['migration'] = $this->migration->getSummaryStats($villageId);
        }
        if (in_array($q, ['household stability', 'household', 'stability'])) {
            $results['household_stability'] = $this->householdStability->getSummaryStats($villageId);
        }
        if (in_array($q, ['community structure', 'structure', 'population groups'])) {
            $results['community_structure'] = $this->communityStructure->getSummaryStats($villageId);
        }

        if (empty($results)) {
            $results = $this->getAllIndicators($villageId);
        }

        return $results;
    }

    public function filter(array $filters, ?int $villageId = null): array
    {
        $results = $this->getAllIndicators($villageId);

        if (!empty($filters['village_id'])) {
            $results = $this->getAllIndicators((int) $filters['village_id']);
        }

        return $results;
    }

    protected function getAverageAge(?int $villageId = null): float
    {
        return \App\Models\Resident::where('status', 'active')
            ->when($villageId, fn($q) => $q->where('village_id', $villageId))
            ->whereNotNull('age')
            ->avg('age') ?? 0;
    }

    public function getExecutiveInsights(?int $villageId = null): array
    {
        $insights = [];
        $indicators = $this->getAllIndicators($villageId);

        $dep = $indicators['dependency'];
        if ($dep['total_dependency_ratio'] > 70) {
            $insights[] = [
                'title' => 'High Dependency Pressure',
                'description' => "The dependency ratio of {$dep['total_dependency_ratio']}% indicates significant economic pressure on the working population. Social welfare programs should be prioritized.",
                'priority' => 'warning',
                'category' => 'Dependency Ratio',
                'icon' => 'exclamation-triangle',
            ];
        } elseif ($dep['total_dependency_ratio'] < 50) {
            $insights[] = [
                'title' => 'Favorable Dependency Balance',
                'description' => "The dependency ratio of {$dep['total_dependency_ratio']}% is favorable, with a strong working-age population supporting dependents.",
                'priority' => 'success',
                'category' => 'Dependency Ratio',
                'icon' => 'check-circle',
            ];
        }

        $den = $indicators['density'];
        if ($den['land_area_sq_km'] > 0) {
            $insights[] = [
                'title' => 'Population Density',
                'description' => "Average density of {$den['population_density']} people/km² across the community.",
                'priority' => 'info',
                'category' => 'Density',
                'icon' => 'building',
            ];
        }

        $gro = $indicators['growth'];
        if ($gro['annual_growth'] > 5) {
            $insights[] = [
                'title' => 'Rapid Population Growth',
                'description' => "Annual growth rate of {$gro['annual_growth']}% requires infrastructure and service expansion planning.",
                'priority' => 'warning',
                'category' => 'Growth',
                'icon' => 'graph-up',
            ];
        } elseif ($gro['annual_growth'] < 0) {
            $insights[] = [
                'title' => 'Population Decline',
                'description' => "Annual growth rate of {$gro['annual_growth']}% indicates population decline, potentially impacting community vitality.",
                'priority' => 'danger',
                'category' => 'Growth',
                'icon' => 'graph-down',
            ];
        }

        $com = $indicators['community_structure'];
        $insights[] = [
            'title' => 'Working-Age Population',
            'description' => "The working-age population accounts for {$com['working_pct']}% of all residents.",
            'priority' => 'primary',
            'category' => 'Community Structure',
            'icon' => 'briefcase',
        ];

        $res = $indicators['residency'];
        if ($res['average_years'] > 8) {
            $insights[] = [
                'title' => 'Community Stability',
                'description' => "Average residency of {$res['average_years']} years indicates strong community bonds and residential stability.",
                'priority' => 'success',
                'category' => 'Residency',
                'icon' => 'house-heart',
            ];
        }

        $mig = $indicators['migration'];
        if ($mig['new_residents_year'] > 0) {
            $insights[] = [
                'title' => 'Migration Activity',
                'description' => "{$mig['new_residents_year']} new residents joined this year with a migration rate of {$mig['migration_rate']}%.",
                'priority' => 'info',
                'category' => 'Migration',
                'icon' => 'people',
            ];
        }

        $hh = $indicators['household_stability'];
        $insights[] = [
            'title' => 'Household Composition',
            'description' => "Average household size is {$hh['avg_members']} members with a stability index of {$hh['stability_index']}.",
            'priority' => 'info',
            'category' => 'Household',
            'icon' => 'house-door',
        ];

        if (!$villageId) {
            $comparison = $this->getDemographicComparison();
            if (count($comparison) >= 2) {
                $highest = collect($comparison)->sortByDesc('dependency_ratio')->first();
                $lowest = collect($comparison)->sortBy('dependency_ratio')->first();

                if ($highest['village_name'] !== $lowest['village_name']) {
                    $insights[] = [
                        'title' => 'Village Comparison',
                        'description' => "{$highest['village_name']} has the highest dependency ratio ({$highest['dependency_ratio']}%), while {$lowest['village_name']} has the lowest ({$lowest['dependency_ratio']}%).",
                        'priority' => 'primary',
                        'category' => 'Comparison',
                        'icon' => 'bar-chart',
                    ];
                }

                $fastestGrowing = collect($comparison)->sortByDesc('annual_growth')->first();
                if ($fastestGrowing['annual_growth'] > 0) {
                    $insights[] = [
                        'title' => 'Growth Leader',
                        'description' => "{$fastestGrowing['village_name']} experienced the highest population growth at {$fastestGrowing['annual_growth']}%.",
                        'priority' => 'info',
                        'category' => 'Growth',
                        'icon' => 'trophy',
                    ];
                }
            }
        }

        return $insights;
    }
}
