<?php

namespace App\Services\Analytics;

use App\Models\Household;
use App\Models\Resident;
use App\Models\Village;
use App\Support\SqlHelper;
use Illuminate\Database\Eloquent\Builder;

class PopulationAnalyticsService
{
    public function __construct(
        protected StatisticsService $stats
    ) {}

    protected function baseQuery(?int $villageId = null): Builder
    {
        $query = Resident::query()->where('approval_status', 'approved');

        if ($villageId) {
            $query->where('village_id', $villageId);
        }

        return $query;
    }

    public function getSystemStats(): array
    {
        $total = Resident::where('status', 'active')->where('approval_status', 'approved')->count();
        $previousMonth = Resident::where('status', 'active')
            ->where('approval_status', 'approved')
            ->where('created_at', '<=', now()->subMonth()->endOfMonth())
            ->count();
        $thisMonth = Resident::where('status', 'active')
            ->where('approval_status', 'approved')
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        return [
            'total' => $total,
            'previous_month' => $previousMonth,
            'previous' => $previousMonth,
            'this_month' => $thisMonth,
            'change' => $this->stats->growthRate($thisMonth, $previousMonth),
        ];
    }

    public function getVillageStats(int $villageId): array
    {
        $total = Resident::where('village_id', $villageId)->where('status', 'active')->where('approval_status', 'approved')->count();
        $previousMonth = Resident::where('village_id', $villageId)
            ->where('status', 'active')
            ->where('approval_status', 'approved')
            ->where('created_at', '<=', now()->subMonth()->endOfMonth())
            ->count();
        $thisMonth = Resident::where('village_id', $villageId)
            ->where('status', 'active')
            ->where('approval_status', 'approved')
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        $male = Resident::where('village_id', $villageId)->where('status', 'active')->where('approval_status', 'approved')->where('gender', 'Male')->count();
        $female = Resident::where('village_id', $villageId)->where('status', 'active')->where('approval_status', 'approved')->where('gender', 'Female')->count();

        return [
            'total' => $total,
            'male' => $male,
            'female' => $female,
            'previous_month' => $previousMonth,
            'previous' => $previousMonth,
            'this_month' => $thisMonth,
            'change' => $this->stats->growthRate($thisMonth, $previousMonth),
        ];
    }

    public function getGenderDistribution(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId)->where('status', 'active');

        $counts = $query->selectRaw('gender, COUNT(*) as count')
            ->groupBy('gender')
            ->pluck('count', 'gender')
            ->toArray();

        $total = array_sum($counts);

        return [
            'data' => $this->stats->frequencyCount($counts),
            'total' => $total,
            'labels' => array_keys($counts),
            'values' => array_values($counts),
        ];
    }

    public function getAgeDistribution(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId)
            ->where('status', 'active')
            ->whereNotNull('age');

        $data = (clone $query)->selectRaw('
            CASE
                WHEN age < 5 THEN \'0-4\'
                WHEN age BETWEEN 5 AND 12 THEN \'5-12\'
                WHEN age BETWEEN 13 AND 17 THEN \'13-17\'
                WHEN age BETWEEN 18 AND 30 THEN \'18-30\'
                WHEN age BETWEEN 31 AND 50 THEN \'31-50\'
                WHEN age BETWEEN 51 AND 65 THEN \'51-65\'
                ELSE \'65+\'
            END as age_group,
            COUNT(*) as count
        ')->groupBy('age_group')->pluck('count', 'age_group')->toArray();

        $total = array_sum($data);

        $childYouth = ($data['0-4'] ?? 0) + ($data['5-12'] ?? 0) + ($data['13-17'] ?? 0);
        $youthAdult = ($data['18-30'] ?? 0);
        $adult = ($data['31-50'] ?? 0) + ($data['51-65'] ?? 0);
        $senior = ($data['65+'] ?? 0);

        return [
            'data' => $this->stats->frequencyCount($data),
            'total' => $total,
            'labels' => array_keys($data),
            'values' => array_values($data),
            'summary' => [
                'children_youth' => $childYouth,
                'youth_adult' => $youthAdult,
                'adult' => $adult,
                'senior' => $senior,
                'children_youth_pct' => $this->stats->percentage($childYouth, $total),
                'youth_adult_pct' => $this->stats->percentage($youthAdult, $total),
                'adult_pct' => $this->stats->percentage($adult, $total),
                'senior_pct' => $this->stats->percentage($senior, $total),
            ],
        ];
    }

    public function getCivilStatusDistribution(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId)->where('status', 'active');

        $counts = $query->selectRaw('COALESCE(civil_status, \'Unspecified\') as cs, COUNT(*) as count')
            ->groupBy('cs')
            ->pluck('count', 'cs')
            ->toArray();

        $total = array_sum($counts);

        return [
            'data' => $this->stats->frequencyCount($counts),
            'total' => $total,
            'labels' => array_keys($counts),
            'values' => array_values($counts),
        ];
    }

    public function getOccupationDistribution(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId)->where('status', 'active');

        $counts = $query->selectRaw('COALESCE(occupation, \'Unspecified\') as occ, COUNT(*) as count')
            ->groupBy('occ')
            ->pluck('count', 'occ')
            ->toArray();

        arsort($counts);
        $total = array_sum($counts);

        return [
            'data' => $this->stats->frequencyCount($counts),
            'total' => $total,
            'labels' => array_keys($counts),
            'values' => array_values($counts),
        ];
    }

    public function getEmploymentDistribution(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId)->where('status', 'active');

        $counts = $query->selectRaw('
            CASE
                WHEN occupation IS NOT NULL AND occupation != \'\' THEN \'Employed\'
                ELSE \'Unemployed\'
            END as emp_status,
            COUNT(*) as count
        ')->groupBy('emp_status')
            ->pluck('count', 'emp_status')
            ->toArray();

        $total = array_sum($counts);

        return [
            'data' => $this->stats->frequencyCount($counts),
            'total' => $total,
            'labels' => array_keys($counts),
            'values' => array_values($counts),
        ];
    }

    public function getEducationDistribution(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId)->where('status', 'active');

        $counts = $query->selectRaw('COALESCE(educational_attainment, \'Unspecified\') as edu, COUNT(*) as count')
            ->groupBy('edu')
            ->pluck('count', 'edu')
            ->toArray();

        $total = array_sum($counts);

        return [
            'data' => $this->stats->frequencyCount($counts),
            'total' => $total,
            'labels' => array_keys($counts),
            'values' => array_values($counts),
        ];
    }

    public function getHouseholdDistribution(?int $villageId = null): array
    {
        $query = Household::withCount(['residents' => fn ($q) => $q->where('status', 'active')]);

        if ($villageId) {
            $query->where('village_id', $villageId);
        }

        $sizes = $query->get()->pluck('residents_count')->filter()->toArray();

        $distribution = [
            '1' => 0,
            '2-3' => 0,
            '4-5' => 0,
            '6-7' => 0,
            '8+' => 0,
        ];

        foreach ($sizes as $size) {
            if ($size == 1) {
                $distribution['1']++;
            } elseif ($size <= 3) {
                $distribution['2-3']++;
            } elseif ($size <= 5) {
                $distribution['4-5']++;
            } elseif ($size <= 7) {
                $distribution['6-7']++;
            } else {
                $distribution['8+']++;
            }
        }

        $total = count($sizes);
        $avgSize = $total > 0 ? round(array_sum($sizes) / $total, 2) : 0;

        return [
            'data' => $this->stats->frequencyCount($distribution),
            'total' => $total,
            'labels' => array_keys($distribution),
            'values' => array_values($distribution),
            'average_size' => $avgSize,
        ];
    }

    public function getMonthlyPopulationTrend(?int $villageId = null, int $months = 12): array
    {
        $query = Resident::where('status', 'active')
            ->where('created_at', '>=', now()->subMonths($months)->startOfMonth());

        if ($villageId) {
            $query->where('village_id', $villageId);
        }

        $data = $query->selectRaw(
            SqlHelper::dateFormat('created_at', '%Y-%m').' as month, COUNT(*) as count'
        )->groupBy('month')->pluck('count', 'month')->toArray();

        $result = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $result[$key] = $data[$key] ?? 0;
        }

        return $result;
    }

    public function getRegistrationTrend(?int $villageId = null, int $months = 12): array
    {
        return $this->getMonthlyPopulationTrend($villageId, $months);
    }

    public function getYearlyTrend(?int $villageId = null, int $years = 5): array
    {
        $query = Resident::where('status', 'active')
            ->where('created_at', '>=', now()->subYears($years)->startOfYear());

        if ($villageId) {
            $query->where('village_id', $villageId);
        }

        $data = $query->selectRaw(
            SqlHelper::dateFormat('created_at', '%Y').' as year, COUNT(*) as count'
        )->groupBy('year')->pluck('count', 'year')->toArray();

        $result = [];
        for ($i = $years - 1; $i >= 0; $i--) {
            $key = now()->subYears($i)->format('Y');
            $result[$key] = $data[$key] ?? 0;
        }

        return $result;
    }

    public function getGrowthAnalysis(?int $villageId = null): array
    {
        $currentMonth = now()->format('Y-m');
        $previousMonth = now()->subMonth()->format('Y-m');

        $currentCount = $this->baseQuery($villageId)
            ->where('status', 'active')
            ->whereRaw(SqlHelper::dateFormat('created_at', '%Y-%m').' = ?', [$currentMonth])
            ->count();

        $previousCount = $this->baseQuery($villageId)
            ->where('status', 'active')
            ->whereRaw(SqlHelper::dateFormat('created_at', '%Y-%m').' = ?', [$previousMonth])
            ->count();

        $currentWeek = now()->startOfWeek()->toDateString();
        $thisWeekCount = $this->baseQuery($villageId)
            ->where('status', 'active')
            ->where('created_at', '>=', $currentWeek)
            ->count();

        $lastWeekStart = now()->subWeek()->startOfWeek()->toDateString();
        $lastWeekEnd = now()->subWeek()->endOfWeek()->toDateString();
        $previousWeekCount = $this->baseQuery($villageId)
            ->where('status', 'active')
            ->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
            ->count();

        $thisQuarter = ceil(now()->month / 3);
        $thisQuarterCount = $this->baseQuery($villageId)
            ->where('status', 'active')
            ->where('created_at', '>=', now()->startOfQuarter())
            ->count();

        $thisYearCount = $this->baseQuery($villageId)
            ->where('status', 'active')
            ->where('created_at', '>=', now()->startOfYear())
            ->count();

        $previousYearCount = $this->baseQuery($villageId)
            ->where('status', 'active')
            ->where('created_at', '>=', now()->subYear()->startOfYear())
            ->where('created_at', '<=', now()->subYear()->endOfYear())
            ->count();

        return [
            'monthly' => [
                'current' => $currentCount,
                'previous' => $previousCount,
                'change' => $currentCount - $previousCount,
                'growth_rate' => $this->stats->growthRate($currentCount, $previousCount),
            ],
            'weekly' => [
                'current' => $thisWeekCount,
                'previous' => $previousWeekCount,
                'change' => $thisWeekCount - $previousWeekCount,
                'growth_rate' => $this->stats->growthRate($thisWeekCount, $previousWeekCount),
            ],
            'quarterly' => [
                'current' => $thisQuarterCount,
            ],
            'yearly' => [
                'current' => $thisYearCount,
                'previous' => $previousYearCount,
                'change' => $thisYearCount - $previousYearCount,
                'growth_rate' => $this->stats->growthRate($thisYearCount, $previousYearCount),
            ],
        ];
    }

    public function getRegistrationStats(?int $villageId = null): array
    {
        $today = now()->toDateString();
        $weekAgo = now()->subWeek()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $yearStart = now()->startOfYear()->toDateString();

        $registeredToday = $this->baseQuery($villageId)
            ->where('status', 'active')
            ->whereDate('created_at', $today)
            ->count();

        $registeredThisWeek = $this->baseQuery($villageId)
            ->where('status', 'active')
            ->where('created_at', '>=', $weekAgo)
            ->count();

        $registeredThisMonth = $this->baseQuery($villageId)
            ->where('status', 'active')
            ->where('created_at', '>=', $monthStart)
            ->count();

        $registeredThisYear = $this->baseQuery($villageId)
            ->where('status', 'active')
            ->where('created_at', '>=', $yearStart)
            ->count();

        return [
            'today' => $registeredToday,
            'this_week' => $registeredThisWeek,
            'this_month' => $registeredThisMonth,
            'this_year' => $registeredThisYear,
            'avg_monthly' => $this->calculateAvgMonthly($villageId),
            'avg_annual' => $this->calculateAvgAnnual($villageId),
        ];
    }

    protected function calculateAvgMonthly(?int $villageId = null): float
    {
        $query = $this->baseQuery($villageId)
            ->where('status', 'active');

        $first = (clone $query)->oldest('created_at')->first();
        $last = (clone $query)->latest('created_at')->first();

        if (! $first || ! $last) {
            return 0;
        }

        $months = max(1, $first->created_at->diffInMonths($last->created_at) + 1);
        $total = $this->baseQuery($villageId)->where('status', 'active')->count();

        return round($total / $months, 1);
    }

    protected function calculateAvgAnnual(?int $villageId = null): float
    {
        $avgMonthly = $this->calculateAvgMonthly($villageId);

        return round($avgMonthly * 12, 1);
    }

    public function getPopulationSummary(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId);

        $totalPopulation = (clone $query)->where('status', 'active')->count();
        $approved = (clone $query)->where('approval_status', 'approved')->count();
        $pending = (clone $query)->whereIn('approval_status', ['pending', 'admin_approved'])->count();
        $rejected = (clone $query)->where('approval_status', 'rejected')->count();

        $registrationStats = $this->getRegistrationStats($villageId);
        $growth = $this->getGrowthAnalysis($villageId);

        return [
            'total_population' => $totalPopulation,
            'approved' => $approved,
            'pending' => $pending,
            'rejected' => $rejected,
            'registered_today' => $registrationStats['today'],
            'registered_this_week' => $registrationStats['this_week'],
            'registered_this_month' => $registrationStats['this_month'],
            'registered_this_year' => $registrationStats['this_year'],
            'avg_monthly_registration' => $registrationStats['avg_monthly'],
            'avg_annual_registration' => $registrationStats['avg_annual'],
            'population_growth' => $growth['monthly']['growth_rate'],
            'weekly_growth' => $growth['weekly']['growth_rate'],
            'monthly_growth' => $growth['monthly']['growth_rate'],
            'yearly_growth' => $growth['yearly']['growth_rate'],
        ];
    }

    public function getVillageComparison(): array
    {
        $villages = Village::where('status', 'active')->get();

        return $villages->map(function ($village) {
            $stats = $this->getVillageStats($village->id);
            $growth = $this->getGrowthAnalysis($village->id);
            $regStats = $this->getRegistrationStats($village->id);
            $households = Household::where('village_id', $village->id)->count();

            $avgHouseholdSize = $households > 0
                ? round($stats['total'] / $households, 2)
                : 0;

            return [
                'village' => $village,
                'total' => $stats['total'],
                'male' => $stats['male'],
                'female' => $stats['female'],
                'change' => $stats['change'],
                'growth_rate' => $growth['monthly']['growth_rate'],
                'avg_household_size' => $avgHouseholdSize,
                'registered_this_month' => $regStats['this_month'],
            ];
        })->toArray();
    }

    public function getVillageComparisonRankings(): array
    {
        $comparison = $this->getVillageComparison();

        $rankings = collect($comparison);

        return [
            'by_population' => $rankings->sortByDesc('total')->values()->toArray(),
            'by_growth' => $rankings->sortByDesc('growth_rate')->values()->toArray(),
            'by_household_size' => $rankings->sortByDesc('avg_household_size')->values()->toArray(),
            'lowest_population' => $rankings->sortBy('total')->values()->toArray(),
        ];
    }

    public function getPopulationInsights(?int $villageId = null): array
    {
        $insights = [];
        $summary = $this->getPopulationSummary($villageId);
        $genderDist = $this->getGenderDistribution($villageId);
        $growth = $this->getGrowthAnalysis($villageId);

        if ($summary['population_growth'] > 0) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'bi-graph-up',
                'message' => "Population increased by {$summary['population_growth']}% this month.",
            ];
        } elseif ($summary['population_growth'] < 0) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'bi-graph-down',
                'message' => 'Population decreased by '.abs($summary['population_growth']).'% this month.',
            ];
        }

        $total = $genderDist['total'];
        if ($total > 0) {
            $malePct = $this->stats->percentage($genderDist['data']['Male']['count'] ?? 0, $total);
            $femalePct = $this->stats->percentage($genderDist['data']['Female']['count'] ?? 0, $total);
            if ($malePct > $femalePct) {
                $insights[] = [
                    'type' => 'info',
                    'icon' => 'bi-gender-male',
                    'message' => "Male residents ({$malePct}%) outnumber female residents ({$femalePct}%).",
                ];
            } else {
                $insights[] = [
                    'type' => 'info',
                    'icon' => 'bi-gender-female',
                    'message' => "Female residents ({$femalePct}%) outnumber male residents ({$malePct}%).",
                ];
            }
        }

        $approvalRate = $total > 0 ? round(($summary['approved'] / $total) * 100, 1) : 0;
        if ($approvalRate > 0) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'bi-check-circle',
                'message' => "Approved residents account for {$approvalRate}% of total registrations.",
            ];
        }

        if (! $villageId) {
            $villages = Village::where('status', 'active')->get();
            $highestPop = null;
            $highestCount = 0;
            foreach ($villages as $v) {
                $count = Resident::where('village_id', $v->id)->where('status', 'active')->count();
                if ($count > $highestCount) {
                    $highestCount = $count;
                    $highestPop = $v->village_name;
                }
            }
            if ($highestPop) {
                $insights[] = [
                    'type' => 'primary',
                    'icon' => 'bi-building',
                    'message' => "{$highestPop} has the highest population with {$highestCount} residents.",
                ];
            }
        }

        if ($summary['registered_today'] > 0) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'bi-person-plus',
                'message' => "{$summary['registered_today']} new resident(s) registered today.",
            ];
        }

        if ($summary['pending'] > 0) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'bi-hourglass-split',
                'message' => "{$summary['pending']} resident registration(s) pending approval.",
            ];
        }

        $growthAnalysis = $this->getGrowthAnalysis($villageId);
        if ($growthAnalysis['weekly']['change'] < 0) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'bi-arrow-down',
                'message' => 'Resident registration decreased this week by '.abs($growthAnalysis['weekly']['change']).'.',
            ];
        }

        return $insights;
    }

    public function getAverageAge(?int $villageId = null): float
    {
        $query = $this->baseQuery($villageId)
            ->where('status', 'active')
            ->whereNotNull('age');

        return (float) $query->avg('age');
    }
}
