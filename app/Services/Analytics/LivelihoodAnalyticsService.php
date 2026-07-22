<?php

namespace App\Services\Analytics;

use App\Models\Resident;
use Illuminate\Database\Eloquent\Builder;

class LivelihoodAnalyticsService
{
    public function __construct(protected StatisticsService $stats) {}

    protected function baseQuery(?int $villageId = null): Builder
    {
        $query = Resident::query()->where('status', 'active');
        if ($villageId) $query->where('village_id', $villageId);
        return $query;
    }

    public function getSummaryStats(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId);
        $total = $query->count();
        $withLivelihood = (clone $query)->whereNotNull('income_source')->where('income_source', '!=', '')->count();
        $top = (clone $query)->selectRaw('income_source, COUNT(*) as count')
            ->whereNotNull('income_source')->where('income_source', '!=', '')
            ->groupBy('income_source')->orderByDesc('count')->first();

        return [
            'total' => $total,
            'with_livelihood' => $withLivelihood,
            'coverage_rate' => $this->stats->percentage($withLivelihood, $total),
            'top_source' => $top?->income_source ?? '—',
            'top_count' => $top?->count ?? 0,
        ];
    }

    public function getLivelihoodDistribution(?int $villageId = null): array
    {
        $counts = $this->baseQuery($villageId)
            ->selectRaw('income_source, COUNT(*) as count')
            ->whereNotNull('income_source')->where('income_source', '!=', '')
            ->groupBy('income_source')->orderByDesc('count')
            ->pluck('count', 'income_source')->toArray();
        return $this->stats->frequencyCount($counts);
    }

    public function getIncomeRangeDistribution(?int $villageId = null): array
    {
        $order = ['Below ₱5,000', '₱5,000 - ₱10,000', '₱10,001 - ₱20,000', '₱20,001 - ₱40,000', '₱40,001 - ₱60,000', '₱60,001 - ₱100,000', 'Above ₱100,000'];
        $counts = $this->baseQuery($villageId)
            ->selectRaw('monthly_income_range, COUNT(*) as count')
            ->whereNotNull('monthly_income_range')->where('monthly_income_range', '!=', '')
            ->groupBy('monthly_income_range')
            ->pluck('count', 'monthly_income_range')->toArray();
        $ordered = [];
        foreach ($order as $r) { if (isset($counts[$r])) $ordered[$r] = $counts[$r]; }
        return $this->stats->frequencyCount($ordered);
    }

    public function getIncomeStats(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId)->whereNotNull('monthly_income');
        $incomes = (clone $query)->pluck('monthly_income')->toArray();
        return [
            'count' => count($incomes),
            'average' => $this->stats->mean($incomes),
            'median' => $this->stats->median($incomes),
            'min' => count($incomes) ? min($incomes) : 0,
            'max' => count($incomes) ? max($incomes) : 0,
        ];
    }

    public function getIncomeByGender(?int $villageId = null): array
    {
        return $this->baseQuery($villageId)
            ->selectRaw('gender, AVG(monthly_income) as avg_income')
            ->whereNotNull('monthly_income')
            ->groupBy('gender')
            ->pluck('avg_income', 'gender')
            ->map(fn($v) => round((float) $v, 2))
            ->toArray();
    }

    public function getVillageComparison(): array
    {
        $villages = \App\Models\Village::where('status', 'active')->get();
        $result = [];
        foreach ($villages as $v) {
            $stats = $this->getSummaryStats($v->id);
            $income = $this->getIncomeStats($v->id);
            $result[$v->village_name] = [
                'village_id' => $v->id, 'village_code' => $v->village_code,
                'total' => $stats['total'], 'top_source' => $stats['top_source'],
                'avg_income' => $income['average'], 'median_income' => $income['median'],
            ];
        }
        return $result;
    }

    public function getInsights(?int $villageId = null): array
    {
        $stats = $this->getSummaryStats($villageId);
        $income = $this->getIncomeStats($villageId);
        $insights = [];
        if ($stats['total'] > 0 && $stats['top_source'] !== '—') {
            $pct = $this->stats->percentage($stats['top_count'], $stats['total']);
            $insights[] = ['type' => 'info', 'message' => "\"{$stats['top_source']}\" is the top livelihood source ({$pct}%)."];
        }
        if ($income['count'] > 0) {
            $insights[] = ['type' => 'info', 'message' => "Average monthly income: ₱" . number_format($income['average'], 2) . " (Median: ₱" . number_format($income['median'], 2) . ")."];
        }
        return $insights;
    }
}
