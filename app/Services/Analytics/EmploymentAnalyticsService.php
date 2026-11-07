<?php

namespace App\Services\Analytics;

use App\Models\Resident;
use Illuminate\Database\Eloquent\Builder;

class EmploymentAnalyticsService
{
    public function __construct(
        protected StatisticsService $stats
    ) {}

    protected function baseQuery(?int $villageId = null): Builder
    {
        $query = Resident::query()->where('status', 'active');

        if ($villageId) {
            $query->where('village_id', $villageId);
        }

        return $query;
    }

    public function getSummaryStats(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId);
        $total = $query->count();

        $employed = (clone $query)->where('employment_status', 'Employed')->count();
        $selfEmployed = (clone $query)->where('employment_status', 'Self-Employed')->count();
        $unemployed = (clone $query)->where('employment_status', 'Unemployed')->count();
        $retired = (clone $query)->where('employment_status', 'Retired')->count();
        $studentWorkers = (clone $query)->where('employment_status', 'Student')->count();
        $ofw = (clone $query)->where('is_ofw', true)->count();

        $incomes = (clone $query)->whereNotNull('monthly_income')->pluck('monthly_income')->toArray();
        $avgIncome = $this->stats->mean($incomes);
        $medianIncome = $this->stats->median($incomes);
        $incomeCount = count($incomes);

        return [
            'total_residents' => $total,
            'employed' => $employed,
            'self_employed' => $selfEmployed,
            'total_employed' => $employed + $selfEmployed,
            'employment_rate' => $this->stats->percentage($employed + $selfEmployed, $total),
            'unemployed' => $unemployed,
            'unemployment_rate' => $this->stats->percentage($unemployed, $total),
            'retired' => $retired,
            'student_workers' => $studentWorkers,
            'ofw' => $ofw,
            'ofw_rate' => $this->stats->percentage($ofw, $total),
            'average_income' => $avgIncome,
            'median_income' => $medianIncome,
            'income_data_count' => $incomeCount,
        ];
    }

    public function getEmploymentStatusDistribution(?int $villageId = null): array
    {
        $counts = $this->baseQuery($villageId)
            ->selectRaw('employment_status, COUNT(*) as count')
            ->whereNotNull('employment_status')
            ->where('employment_status', '!=', '')
            ->groupBy('employment_status')
            ->pluck('count', 'employment_status')
            ->toArray();

        return $this->stats->frequencyCount($counts);
    }

    public function getOccupationDistribution(?int $villageId = null): array
    {
        $counts = $this->baseQuery($villageId)
            ->selectRaw('occupation, COUNT(*) as count')
            ->whereNotNull('occupation')
            ->where('occupation', '!=', '')
            ->groupBy('occupation')
            ->orderByDesc('count')
            ->limit(15)
            ->pluck('count', 'occupation')
            ->toArray();

        return $this->stats->frequencyCount($counts);
    }

    public function getIncomeSourceDistribution(?int $villageId = null): array
    {
        $counts = $this->baseQuery($villageId)
            ->selectRaw('income_source, COUNT(*) as count')
            ->whereNotNull('income_source')
            ->where('income_source', '!=', '')
            ->groupBy('income_source')
            ->pluck('count', 'income_source')
            ->toArray();

        return $this->stats->frequencyCount($counts);
    }

    public function getIncomeRangeDistribution(?int $villageId = null): array
    {
        $counts = $this->baseQuery($villageId)
            ->selectRaw('monthly_income_range, COUNT(*) as count')
            ->whereNotNull('monthly_income_range')
            ->where('monthly_income_range', '!=', '')
            ->groupBy('monthly_income_range')
            ->pluck('count', 'monthly_income_range')
            ->toArray();

        $order = ['Below ₱5,000', '₱5,000 - ₱10,000', '₱10,001 - ₱20,000', '₱20,001 - ₱40,000', '₱40,001 - ₱60,000', '₱60,001 - ₱100,000', 'Above ₱100,000'];
        $ordered = [];
        foreach ($order as $range) {
            if (isset($counts[$range])) {
                $ordered[$range] = $counts[$range];
            }
        }

        return $this->stats->frequencyCount($ordered);
    }

    public function getIncomeByGender(?int $villageId = null): array
    {
        $rows = $this->baseQuery($villageId)
            ->selectRaw('gender, AVG(monthly_income) as avg_income, COUNT(*) as count')
            ->whereNotNull('monthly_income')
            ->groupBy('gender')
            ->pluck('avg_income', 'gender')
            ->toArray();

        return array_map(fn($v) => round((float) $v, 2), $rows);
    }

    public function getIncomeByEmployment(?int $villageId = null): array
    {
        $rows = $this->baseQuery($villageId)
            ->selectRaw('employment_status, AVG(monthly_income) as avg_income, COUNT(monthly_income) as count')
            ->whereNotNull('monthly_income')
            ->groupBy('employment_status')
            ->pluck('avg_income', 'employment_status')
            ->toArray();

        return array_map(fn($v) => round((float) $v, 2), $rows);
    }

    public function getExperienceDistribution(?int $villageId = null): array
    {
        $counts = $this->baseQuery($villageId)
            ->selectRaw("CASE
                WHEN years_of_experience IS NULL THEN 'Not Specified'
                WHEN years_of_experience = 0 THEN 'None (0)'
                WHEN years_of_experience BETWEEN 1 AND 3 THEN '1-3 years'
                WHEN years_of_experience BETWEEN 4 AND 10 THEN '4-10 years'
                WHEN years_of_experience BETWEEN 11 AND 20 THEN '11-20 years'
                ELSE '20+ years'
            END as exp_range, COUNT(*) as count")
            ->groupBy('exp_range')
            ->pluck('count', 'exp_range')
            ->toArray();

        return $this->stats->frequencyCount($counts);
    }

    public function getJobTitleDistribution(?int $villageId = null): array
    {
        $counts = $this->baseQuery($villageId)
            ->selectRaw('job_title, COUNT(*) as count')
            ->whereNotNull('job_title')
            ->where('job_title', '!=', '')
            ->groupBy('job_title')
            ->orderByDesc('count')
            ->limit(15)
            ->pluck('count', 'job_title')
            ->toArray();

        return $this->stats->frequencyCount($counts);
    }

    public function getVillageComparison(): array
    {
        $villages = \App\Models\Village::where('status', 'active')->get();
        $result = [];

        foreach ($villages as $village) {
            $stats = $this->getSummaryStats($village->id);
            $result[$village->village_name] = [
                'village_id' => $village->id,
                'village_code' => $village->village_code,
                'total' => $stats['total_residents'],
                'employment_rate' => $stats['employment_rate'],
                'unemployment_rate' => $stats['unemployment_rate'],
                'ofw_rate' => $stats['ofw_rate'],
                'average_income' => $stats['average_income'],
                'median_income' => $stats['median_income'],
            ];
        }

        return $result;
    }

    public function getInsights(?int $villageId = null): array
    {
        $stats = $this->getSummaryStats($villageId);
        $insights = [];

        if ($stats['total_residents'] > 0) {
            if ($stats['employment_rate'] >= 80) {
                $insights[] = ['type' => 'success', 'message' => "Strong employment rate of {$stats['employment_rate']}%."];
            } elseif ($stats['unemployment_rate'] > 30) {
                $insights[] = ['type' => 'warning', 'message' => "High unemployment rate of {$stats['unemployment_rate']}% — consider livelihood programs."];
            }

            if ($stats['ofw_rate'] > 10) {
                $insights[] = ['type' => 'info', 'message' => "{$stats['ofw']} OFWs ({$stats['ofw_rate']}%) — significant remittance economy."];
            }

            if ($stats['average_income'] > 0) {
                $insights[] = ['type' => 'info', 'message' => "Average monthly income: ₱" . number_format($stats['average_income'], 2) . " (Median: ₱" . number_format($stats['median_income'], 2) . ")."];
            }
        }

        return $insights;
    }
}
