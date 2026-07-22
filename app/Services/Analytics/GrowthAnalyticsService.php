<?php

namespace App\Services\Analytics;

use App\Models\Resident;
use App\Models\Village;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class GrowthAnalyticsService
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
        $totalNow = $this->baseQuery($villageId)->count();
        $totalLastMonth = Resident::where('status', 'active')
            ->when($villageId, fn($q) => $q->where('village_id', $villageId))
            ->where('created_at', '<=', now()->subMonth()->endOfMonth())
            ->count();
        $totalLastYear = Resident::where('status', 'active')
            ->when($villageId, fn($q) => $q->where('village_id', $villageId))
            ->where('created_at', '<=', now()->subYear()->endOfMonth())
            ->count();

        $dailyGrowth = $this->getGrowthForPeriod($villageId, now()->subDay(), now());
        $weeklyGrowth = $this->getGrowthForPeriod($villageId, now()->subWeek(), now());
        $monthlyGrowth = $this->getGrowthForPeriod($villageId, now()->subMonth(), now());
        $quarterlyGrowth = $this->getGrowthForPeriod($villageId, now()->subMonths(3), now());
        $annualGrowth = $this->stats->growthRate($totalNow, $totalLastYear);

        return [
            'total_population' => $totalNow,
            'previous_month' => $totalLastMonth,
            'previous_year' => $totalLastYear,
            'daily_growth' => $dailyGrowth,
            'weekly_growth' => $weeklyGrowth,
            'monthly_growth' => $monthlyGrowth,
            'quarterly_growth' => $quarterlyGrowth,
            'annual_growth' => $annualGrowth,
            'monthly_change' => $totalNow - $totalLastMonth,
            'yearly_change' => $totalNow - $totalLastYear,
        ];
    }

    protected function getGrowthForPeriod(?int $villageId, $from, $to): float
    {
        $count = Resident::where('status', 'active')
            ->when($villageId, fn($q) => $q->where('village_id', $villageId))
            ->where('created_at', '>=', $from)
            ->where('created_at', '<=', $to)
            ->count();
        return $count;
    }

    public function getMonthlyTrend(?int $villageId = null, int $months = 12): array
    {
        $trend = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthLabel = $date->format('M Y');
            $count = Resident::where('status', 'active')
                ->when($villageId, fn($q) => $q->where('village_id', $villageId))
                ->where('created_at', '<=', $date->copy()->endOfMonth())
                ->count();

            $prevCount = Resident::where('status', 'active')
                ->when($villageId, fn($q) => $q->where('village_id', $villageId))
                ->where('created_at', '<=', $date->copy()->subMonth()->endOfMonth())
                ->count();

            $growth = $this->stats->growthRate($count, $prevCount);

            $trend[] = [
                'month' => $monthLabel,
                'population' => $count,
                'growth_rate' => $growth,
                'change' => $count - $prevCount,
            ];
        }

        return $trend;
    }

    public function getQuarterlyTrend(?int $villageId = null): array
    {
        $trend = [];
        for ($i = 7; $i >= 0; $i--) {
            $quarterStart = now()->subQuarters($i)->startOfQuarter();
            $quarterLabel = 'Q' . $quarterStart->quarter . ' ' . $quarterStart->year;
            $count = Resident::where('status', 'active')
                ->when($villageId, fn($q) => $q->where('village_id', $villageId))
                ->where('created_at', '<=', $quarterStart->copy()->endOfQuarter())
                ->count();

            $trend[] = [
                'quarter' => $quarterLabel,
                'population' => $count,
            ];
        }

        return $trend;
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
                'total_population' => $summary['total_population'],
                'monthly_growth' => $summary['monthly_growth'],
                'annual_growth' => $summary['annual_growth'],
                'monthly_change' => $summary['monthly_change'],
            ];
        }

        return collect($results)->sortByDesc('annual_growth')->values()->toArray();
    }

    public function getInsights(?int $villageId = null): array
    {
        $insights = [];
        $summary = $this->getSummaryStats($villageId);

        if ($summary['annual_growth'] > 5) {
            $insights[] = [
                'title' => 'High Growth Rate',
                'description' => "Annual population growth of {$summary['annual_growth']}% indicates rapid expansion.",
                'priority' => 'warning',
                'icon' => 'arrow-up-right',
            ];
        } elseif ($summary['annual_growth'] < 0) {
            $insights[] = [
                'title' => 'Population Decline',
                'description' => "Annual growth rate of {$summary['annual_growth']}% indicates population decline.",
                'priority' => 'danger',
                'icon' => 'arrow-down-right',
            ];
        }

        if (!$villageId) {
            $comparison = $this->getVillageComparison();
            if (!empty($comparison)) {
                $highest = collect($comparison)->first();
                $insights[] = [
                    'title' => 'Fastest Growing Village',
                    'description' => "{$highest['village_name']} experienced the highest annual population growth at {$highest['annual_growth']}%.",
                    'priority' => 'primary',
                    'icon' => 'graph-up',
                ];
            }
        }

        return $insights;
    }
}
