<?php

namespace App\Services\Analytics;

use App\Models\Household;
use App\Models\Resident;
use App\Models\Village;

class DemographicAnalyticsService
{
    public function __construct(
        protected StatisticsService $stats,
        protected PopulationAnalyticsService $population,
        protected HouseholdAnalyticsService $household
    ) {}

    protected function baseQuery(?int $villageId = null)
    {
        $query = Resident::query();
        if ($villageId) {
            $query->where('village_id', $villageId);
        }
        return $query;
    }

    public function getDemographicSummary(?int $villageId = null): array
    {
        $totalPopulation = $this->baseQuery($villageId)->where('status', 'active')->count();
        $totalHouseholds = Household::when($villageId, fn($q) => $q->where('village_id', $villageId))->count();
        $male = $this->baseQuery($villageId)->where('status', 'active')->where('gender', 'Male')->count();
        $female = $this->baseQuery($villageId)->where('status', 'active')->where('gender', 'Female')->count();
        $seniors = $this->baseQuery($villageId)->where('status', 'active')->where('age', '>=', 60)->count();
        $youth = $this->baseQuery($villageId)->where('status', 'active')->whereBetween('age', [15, 30])->count();
        $children = $this->baseQuery($villageId)->where('status', 'active')->where('age', '<', 15)->count();

        $avgHouseholdSize = $totalHouseholds > 0 ? round($totalPopulation / $totalHouseholds, 2) : 0;

        return [
            'total_population' => $totalPopulation,
            'total_households' => $totalHouseholds,
            'male' => $male,
            'female' => $female,
            'seniors' => $seniors,
            'youth' => $youth,
            'children' => $children,
            'average_household_size' => $avgHouseholdSize,
            'senior_pct' => $this->stats->percentage($seniors, $totalPopulation),
            'youth_pct' => $this->stats->percentage($youth, $totalPopulation),
            'children_pct' => $this->stats->percentage($children, $totalPopulation),
        ];
    }

    public function getFineAgeDistribution(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId)
            ->where('status', 'active')
            ->whereNotNull('age');

        $counts = (clone $query)->selectRaw('
            CASE
                WHEN age < 5 THEN \'0-4\'
                WHEN age BETWEEN 5 AND 9 THEN \'5-9\'
                WHEN age BETWEEN 10 AND 14 THEN \'10-14\'
                WHEN age BETWEEN 15 AND 19 THEN \'15-19\'
                WHEN age BETWEEN 20 AND 24 THEN \'20-24\'
                WHEN age BETWEEN 25 AND 29 THEN \'25-29\'
                WHEN age BETWEEN 30 AND 34 THEN \'30-34\'
                WHEN age BETWEEN 35 AND 39 THEN \'35-39\'
                WHEN age BETWEEN 40 AND 44 THEN \'40-44\'
                WHEN age BETWEEN 45 AND 49 THEN \'45-49\'
                WHEN age BETWEEN 50 AND 54 THEN \'50-54\'
                WHEN age BETWEEN 55 AND 59 THEN \'55-59\'
                WHEN age BETWEEN 60 AND 64 THEN \'60-64\'
                WHEN age BETWEEN 65 AND 69 THEN \'65-69\'
                WHEN age BETWEEN 70 AND 74 THEN \'70-74\'
                ELSE \'75+\'
            END as age_group,
            COUNT(*) as count
        ')->groupBy('age_group')->pluck('count', 'age_group')->toArray();

        $orderedGroups = [
            '0-4', '5-9', '10-14', '15-19', '20-24', '25-29',
            '30-34', '35-39', '40-44', '45-49', '50-54', '55-59',
            '60-64', '65-69', '70-74', '75+'
        ];

        $ordered = [];
        foreach ($orderedGroups as $g) {
            $ordered[$g] = $counts[$g] ?? 0;
        }

        $total = array_sum($ordered);

        $maleData = (clone $this->baseQuery($villageId)->where('status', 'active')->where('gender', 'Male')->whereNotNull('age'))->selectRaw('
            CASE
                WHEN age < 5 THEN \'0-4\'
                WHEN age BETWEEN 5 AND 9 THEN \'5-9\'
                WHEN age BETWEEN 10 AND 14 THEN \'10-14\'
                WHEN age BETWEEN 15 AND 19 THEN \'15-19\'
                WHEN age BETWEEN 20 AND 24 THEN \'20-24\'
                WHEN age BETWEEN 25 AND 29 THEN \'25-29\'
                WHEN age BETWEEN 30 AND 34 THEN \'30-34\'
                WHEN age BETWEEN 35 AND 39 THEN \'35-39\'
                WHEN age BETWEEN 40 AND 44 THEN \'40-44\'
                WHEN age BETWEEN 45 AND 49 THEN \'45-49\'
                WHEN age BETWEEN 50 AND 54 THEN \'50-54\'
                WHEN age BETWEEN 55 AND 59 THEN \'55-59\'
                WHEN age BETWEEN 60 AND 64 THEN \'60-64\'
                WHEN age BETWEEN 65 AND 69 THEN \'65-69\'
                WHEN age BETWEEN 70 AND 74 THEN \'70-74\'
                ELSE \'75+\'
            END as age_group,
            COUNT(*) as count
        ')->groupBy('age_group')->pluck('count', 'age_group')->toArray();

        $femaleData = (clone $this->baseQuery($villageId)->where('status', 'active')->where('gender', 'Female')->whereNotNull('age'))->selectRaw('
            CASE
                WHEN age < 5 THEN \'0-4\'
                WHEN age BETWEEN 5 AND 9 THEN \'5-9\'
                WHEN age BETWEEN 10 AND 14 THEN \'10-14\'
                WHEN age BETWEEN 15 AND 19 THEN \'15-19\'
                WHEN age BETWEEN 20 AND 24 THEN \'20-24\'
                WHEN age BETWEEN 25 AND 29 THEN \'25-29\'
                WHEN age BETWEEN 30 AND 34 THEN \'30-34\'
                WHEN age BETWEEN 35 AND 39 THEN \'35-39\'
                WHEN age BETWEEN 40 AND 44 THEN \'40-44\'
                WHEN age BETWEEN 45 AND 49 THEN \'45-49\'
                WHEN age BETWEEN 50 AND 54 THEN \'50-54\'
                WHEN age BETWEEN 55 AND 59 THEN \'55-59\'
                WHEN age BETWEEN 60 AND 64 THEN \'60-64\'
                WHEN age BETWEEN 65 AND 69 THEN \'65-69\'
                WHEN age BETWEEN 70 AND 74 THEN \'70-74\'
                ELSE \'75+\'
            END as age_group,
            COUNT(*) as count
        ')->groupBy('age_group')->pluck('count', 'age_group')->toArray();

        $maleValues = [];
        $femaleValues = [];
        foreach ($orderedGroups as $g) {
            $maleValues[] = $maleData[$g] ?? 0;
            $femaleValues[] = -($femaleData[$g] ?? 0);
        }

        return [
            'data' => $this->stats->frequencyCount($ordered),
            'total' => $total,
            'labels' => array_keys($ordered),
            'values' => array_values($ordered),
            'male_values' => $maleValues,
            'female_values' => $femaleValues,
            'male_labels' => $maleValues,
            'female_labels' => array_map(fn($v) => abs($v), $femaleValues),
        ];
    }

    public function getSpecialPopulation(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId)->where('status', 'active');
        $total = (clone $query)->count();

        $seniorCitizens = (clone $query)->where('age', '>=', 60)->count();
        $youth = (clone $query)->whereBetween('age', [15, 30])->count();
        $children = (clone $query)->where('age', '<', 15)->count();
        $pwd = 0;
        $soloParents = 0;
        $indigenous = 0;
        $veterans = 0;

        return [
            'total' => $total,
            'senior_citizens' => [
                'count' => $seniorCitizens,
                'percentage' => $this->stats->percentage($seniorCitizens, $total),
            ],
            'youth' => [
                'count' => $youth,
                'percentage' => $this->stats->percentage($youth, $total),
            ],
            'children' => [
                'count' => $children,
                'percentage' => $this->stats->percentage($children, $total),
            ],
            'pwd' => [
                'count' => $pwd,
                'percentage' => 0,
            ],
            'solo_parents' => [
                'count' => $soloParents,
                'percentage' => 0,
            ],
            'indigenous' => [
                'count' => $indigenous,
                'percentage' => 0,
            ],
            'veterans' => [
                'count' => $veterans,
                'percentage' => 0,
            ],
        ];
    }

    public function getNationalityDistribution(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId)->where('status', 'active');

        $counts = $query->selectRaw('
            COALESCE(nationality, \'Filipino\') as nat,
            COUNT(*) as count
        ')->groupBy('nat')->pluck('count', 'nat')->toArray();

        $total = array_sum($counts);

        return [
            'data' => $this->stats->frequencyCount($counts),
            'total' => $total,
            'labels' => array_keys($counts),
            'values' => array_values($counts),
        ];
    }

    public function getResidencyAnalytics(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId)->where('status', 'active');

        $residents = (clone $query)->select('created_at')->get();
        $years = $residents->map(fn($r) => $r->created_at ? now()->diffInYears($r->created_at) : 0)->toArray();

        $durationBuckets = [
            'Less than 1 Year' => 0,
            '1-5 Years' => 0,
            '6-10 Years' => 0,
            '11-20 Years' => 0,
            'More than 20 Years' => 0,
        ];

        foreach ($years as $y) {
            if ($y < 1) $durationBuckets['Less than 1 Year']++;
            elseif ($y <= 5) $durationBuckets['1-5 Years']++;
            elseif ($y <= 10) $durationBuckets['6-10 Years']++;
            elseif ($y <= 20) $durationBuckets['11-20 Years']++;
            else $durationBuckets['More than 20 Years']++;
        }

        $total = count($years);
        $avgYears = $total > 0 ? round(array_sum($years) / $total, 1) : 0;
        $newResidents = $this->baseQuery($villageId)->where('status', 'active')
            ->where('created_at', '>=', now()->subYear())->count();
        $longTermResidents = $this->baseQuery($villageId)->where('status', 'active')
            ->where('created_at', '<=', now()->subYears(5))->count();

        return [
            'data' => $this->stats->frequencyCount($durationBuckets),
            'total' => $total,
            'labels' => array_keys($durationBuckets),
            'values' => array_values($durationBuckets),
            'average_years' => $avgYears,
            'new_residents' => $newResidents,
            'long_term_residents' => $longTermResidents,
        ];
    }

    public function getVillageComparison(): \Illuminate\Support\Collection
    {
        $villages = Village::where('status', 'active')->get();

        return $villages->map(function ($village) {
            $summary = $this->getDemographicSummary($village->id);
            return [
                'village' => $village,
                'total_population' => $summary['total_population'],
                'total_households' => $summary['total_households'],
                'male' => $summary['male'],
                'female' => $summary['female'],
                'seniors' => $summary['seniors'],
                'youth' => $summary['youth'],
                'children' => $summary['children'],
                'average_household_size' => $summary['average_household_size'],
            ];
        });
    }

    public function getDemographicInsights(?int $villageId = null): array
    {
        $insights = [];
        $summary = $this->getDemographicSummary($villageId);
        $genderDist = $this->population->getGenderDistribution($villageId);
        $special = $this->getSpecialPopulation($villageId);
        $residency = $this->getResidencyAnalytics($villageId);

        $total = $summary['total_population'];
        if ($total > 0) {
            $malePct = $this->stats->percentage($summary['male'], $total);
            $femalePct = $this->stats->percentage($summary['female'], $total);

            if (abs($malePct - $femalePct) < 5) {
                $insights[] = [
                    'type' => 'info',
                    'icon' => 'bi-gender-ambiguous',
                    'message' => "Male and female populations are nearly balanced ({$malePct}% / {$femalePct}%).",
                ];
            } elseif ($malePct > $femalePct) {
                $insights[] = [
                    'type' => 'info',
                    'icon' => 'bi-gender-male',
                    'message' => "Male population ({$malePct}%) exceeds female population ({$femalePct}%).",
                ];
            } else {
                $insights[] = [
                    'type' => 'info',
                    'icon' => 'bi-gender-female',
                    'message' => "Female population ({$femalePct}%) exceeds male population ({$malePct}%).",
                ];
            }
        }

        if ($summary['seniors'] > 0) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'bi-person-workspace',
                'message' => "Senior citizens comprise {$special['senior_citizens']['percentage']}% of the population ({$summary['seniors']}).",
            ];
        }

        if ($summary['average_household_size'] > 0) {
            $insights[] = [
                'type' => 'info',
                'icon' => 'bi-house-door',
                'message' => "Average household size is {$summary['average_household_size']} members.",
            ];
        }

        if ($residency['average_years'] > 0) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'bi-clock-history',
                'message' => "Average years of residency is {$residency['average_years']} years.",
            ];
        }

        if ($residency['new_residents'] > 0) {
            $insights[] = [
                'type' => 'primary',
                'icon' => 'bi-person-plus',
                'message' => "{$residency['new_residents']} new resident(s) registered within the past year.",
            ];
        }

        if (!$villageId) {
            $comparison = $this->getVillageComparison();
            if (!empty($comparison)) {
                $highestPop = collect($comparison)->sortByDesc('total_population')->first();
                $lowestPop = collect($comparison)->sortBy('total_population')->first();
                if ($highestPop) {
                    $insights[] = [
                        'type' => 'primary',
                        'icon' => 'bi-building',
                        'message' => "{$highestPop['village']->village_name} has the highest population with {$highestPop['total_population']} residents.",
                    ];
                }
                if ($lowestPop && $lowestPop['village']->id !== $highestPop['village']->id) {
                    $insights[] = [
                        'type' => 'secondary',
                        'icon' => 'bi-building',
                        'message' => "{$lowestPop['village']->village_name} has the lowest population with {$lowestPop['total_population']} residents.",
                    ];
                }
            }
        }

        if ($summary['youth'] > 0 && $total > 0) {
            $youthPct = round(($summary['youth'] / $total) * 100, 1);
            $insights[] = [
                'type' => 'info',
                'icon' => 'bi-person-video',
                'message' => "Youth (ages 15-30) comprise {$youthPct}% of the population ({$summary['youth']}).",
            ];
        }

        if ($summary['children'] > 0 && $total > 0) {
            $childPct = round(($summary['children'] / $total) * 100, 1);
            $insights[] = [
                'type' => 'info',
                'icon' => 'bi-emoji-smile',
                'message' => "Children (under 15) comprise {$childPct}% of the population ({$summary['children']}).",
            ];
        }

        return $insights;
    }
}
