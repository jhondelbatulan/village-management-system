<?php

namespace App\Services\Analytics;

use App\Models\Resident;
use App\Models\Village;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class MigrationAnalyticsService
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
        $total = $this->baseQuery($villageId)->count();
        $newThisMonth = $this->baseQuery($villageId)
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();
        $newThisYear = $this->baseQuery($villageId)
            ->where('created_at', '>=', now()->startOfYear())
            ->count();

        $shortTerm = $this->baseQuery($villageId)
            ->where(function ($q) {
                $q->where('years_of_residency', '<', 1)
                    ->orWhereNull('years_of_residency');
            })
            ->count();
        $midTerm = $this->baseQuery($villageId)
            ->whereBetween('years_of_residency', [1, 5])
            ->count();
        $longTerm = $this->baseQuery($villageId)
            ->where('years_of_residency', '>', 5)
            ->count();

        $totalPrevYear = Resident::where('status', 'active')
            ->when($villageId, fn($q) => $q->where('village_id', $villageId))
            ->where('created_at', '<=', now()->subYear()->endOfMonth())
            ->count();

        $migrationRate = $this->stats->growthRate($total, $totalPrevYear);

        return [
            'total_population' => $total,
            'new_residents_month' => $newThisMonth,
            'new_residents_year' => $newThisYear,
            'short_term' => $shortTerm,
            'mid_term' => $midTerm,
            'long_term' => $longTerm,
            'migration_rate' => $migrationRate,
        ];
    }

    public function getMonthlyTrend(?int $villageId = null, int $months = 12): array
    {
        $trend = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthLabel = $date->format('M Y');

            $newResidents = Resident::where('status', 'active')
                ->when($villageId, fn($q) => $q->where('village_id', $villageId))
                ->where('created_at', '>=', $date->copy()->startOfMonth())
                ->where('created_at', '<=', $date->copy()->endOfMonth())
                ->count();

            $trend[] = [
                'month' => $monthLabel,
                'new_residents' => $newResidents,
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
                'new_residents_year' => $summary['new_residents_year'],
                'migration_rate' => $summary['migration_rate'],
                'long_term' => $summary['long_term'],
            ];
        }

        return collect($results)->sortByDesc('migration_rate')->values()->toArray();
    }

    public function getInsights(?int $villageId = null): array
    {
        $insights = [];
        $summary = $this->getSummaryStats($villageId);

        if ($summary['migration_rate'] > 10) {
            $insights[] = [
                'title' => 'High Migration Inflow',
                'description' => "Migration rate of {$summary['migration_rate']}% indicates significant population movement into the area.",
                'priority' => 'info',
                'icon' => 'people',
            ];
        }

        if ($summary['long_term'] > $summary['short_term']) {
            $insights[] = [
                'title' => 'Predominantly Long-term Residents',
                'description' => "{$summary['long_term']} residents have been in the community for more than 5 years, indicating stability.",
                'priority' => 'success',
                'icon' => 'house-heart',
            ];
        }

        return $insights;
    }
}
