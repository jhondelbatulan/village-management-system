<?php

namespace App\Services\Analytics;

use App\Models\Resident;
use App\Models\Village;
use Illuminate\Database\Eloquent\Builder;

class DependencyRatioService
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
        $ages = $this->baseQuery($villageId)->pluck('age')->filter()->values()->toArray();
        $total = count($ages);

        $child = collect($ages)->filter(fn($a) => $a >= 0 && $a <= 14)->count();
        $working = collect($ages)->filter(fn($a) => $a >= 15 && $a <= 64)->count();
        $senior = collect($ages)->filter(fn($a) => $a >= 65)->count();
        $dependent = $child + $senior;

        $childRatio = $working > 0 ? round(($child / $working) * 100, 2) : 0;
        $seniorRatio = $working > 0 ? round(($senior / $working) * 100, 2) : 0;
        $totalRatio = $working > 0 ? round(($dependent / $working) * 100, 2) : 0;

        return [
            'total_population' => $total,
            'child_population' => $child,
            'working_population' => $working,
            'senior_population' => $senior,
            'dependent_population' => $dependent,
            'child_dependency_ratio' => $childRatio,
            'senior_dependency_ratio' => $seniorRatio,
            'total_dependency_ratio' => $totalRatio,
            'child_pct' => $this->stats->percentage($child, $total),
            'working_pct' => $this->stats->percentage($working, $total),
            'senior_pct' => $this->stats->percentage($senior, $total),
        ];
    }

    public function getAgeGroupDistribution(?int $villageId = null): array
    {
        $ages = $this->baseQuery($villageId)->pluck('age')->filter()->values()->toArray();

        $groups = [
            '0-5 (Infant/Child)' => 0,
            '6-14 (Youth)' => 0,
            '15-24 (Young Adult)' => 0,
            '25-34 (Adult)' => 0,
            '35-44 (Middle-Aged)' => 0,
            '45-54 (Senior Adult)' => 0,
            '55-59 (Pre-Retirement)' => 0,
            '60-64 (Early Senior)' => 0,
            '65-74 (Senior)' => 0,
            '75+ (Elderly)' => 0,
        ];

        foreach ($ages as $age) {
            if ($age <= 5) $groups['0-5 (Infant/Child)']++;
            elseif ($age <= 14) $groups['6-14 (Youth)']++;
            elseif ($age <= 24) $groups['15-24 (Young Adult)']++;
            elseif ($age <= 34) $groups['25-34 (Adult)']++;
            elseif ($age <= 44) $groups['35-44 (Middle-Aged)']++;
            elseif ($age <= 54) $groups['45-54 (Senior Adult)']++;
            elseif ($age <= 59) $groups['55-59 (Pre-Retirement)']++;
            elseif ($age <= 64) $groups['60-64 (Early Senior)']++;
            elseif ($age <= 74) $groups['65-74 (Senior)']++;
            else $groups['75+ (Elderly)']++;
        }

        return $this->stats->frequencyCount($groups);
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
                'total_dependency_ratio' => $summary['total_dependency_ratio'],
                'child_dependency_ratio' => $summary['child_dependency_ratio'],
                'senior_dependency_ratio' => $summary['senior_dependency_ratio'],
                'working_population' => $summary['working_population'],
                'dependent_population' => $summary['dependent_population'],
            ];
        }

        return $results;
    }

    public function getTrend(?int $villageId = null, int $months = 12): array
    {
        $trend = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthLabel = $date->format('M Y');
            $query = Resident::where('status', 'active')
                ->where('created_at', '<=', $date->copy()->endOfMonth());

            if ($villageId) {
                $query->where('village_id', $villageId);
            }

            $ages = $query->pluck('age')->filter()->values()->toArray();
            $working = collect($ages)->filter(fn($a) => $a >= 15 && $a <= 64)->count();
            $dependent = collect($ages)->filter(fn($a) => $a < 15 || $a >= 65)->count();

            $ratio = $working > 0 ? round(($dependent / $working) * 100, 2) : 0;

            $trend[] = [
                'month' => $monthLabel,
                'ratio' => $ratio,
                'working' => $working,
                'dependent' => $dependent,
            ];
        }

        return $trend;
    }

    public function getInsights(?int $villageId = null): array
    {
        $insights = [];
        $summary = $this->getSummaryStats($villageId);

        if ($summary['total_dependency_ratio'] > 70) {
            $insights[] = [
                'title' => 'High Dependency Ratio',
                'description' => "The total dependency ratio of {$summary['total_dependency_ratio']}% indicates significant economic pressure on the working population.",
                'priority' => 'warning',
                'icon' => 'exclamation-triangle',
            ];
        } elseif ($summary['total_dependency_ratio'] > 50) {
            $insights[] = [
                'title' => 'Moderate Dependency Ratio',
                'description' => "The total dependency ratio of {$summary['total_dependency_ratio']}% indicates moderate economic pressure.",
                'priority' => 'info',
                'icon' => 'info-circle',
            ];
        } else {
            $insights[] = [
                'title' => 'Low Dependency Ratio',
                'description' => "The total dependency ratio of {$summary['total_dependency_ratio']}% indicates a favorable working-to-dependent population balance.",
                'priority' => 'success',
                'icon' => 'check-circle',
            ];
        }

        if ($summary['child_dependency_ratio'] > $summary['senior_dependency_ratio']) {
            $insights[] = [
                'title' => 'Child Dependency Dominates',
                'description' => "Child dependency ({$summary['child_dependency_ratio']}%) exceeds senior dependency ({$summary['senior_dependency_ratio']}%), suggesting focus on education and youth programs.",
                'priority' => 'info',
                'icon' => 'mortarboard',
            ];
        }

        if (!$villageId) {
            $comparison = $this->getVillageComparison();
            if (!empty($comparison)) {
                $highest = collect($comparison)->sortByDesc('total_dependency_ratio')->first();
                $lowest = collect($comparison)->sortBy('total_dependency_ratio')->first();
                $insights[] = [
                    'title' => 'Village Comparison',
                    'description' => "{$highest['village_name']} has the highest dependency ratio ({$highest['total_dependency_ratio']}%), while {$lowest['village_name']} has the lowest ({$lowest['total_dependency_ratio']}%).",
                    'priority' => 'primary',
                    'icon' => 'bar-chart',
                ];
            }
        }

        return $insights;
    }
}
