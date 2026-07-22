<?php

namespace App\Services\Analytics;

use App\Models\Household;
use App\Models\Village;
use Illuminate\Support\Facades\DB;

class PaymentReliabilityService extends StatisticsService
{
    public function __construct(
        protected PaymentBehaviorService $behaviorService
    ) {}

    public function getVillageDistribution(int $villageId): array
    {
        return $this->cached("reliability:distribution:{$villageId}", function () use ($villageId) {
            $households = Household::where('village_id', $villageId)
                ->where('status', 'active')
                ->where('approval_status', 'approved')
                ->whereNotNull('payment_behavior')
                ->get();

            $distribution = [
                'excellent' => ['count' => 0, 'percentage' => 0, 'avg_score' => 0],
                'good' => ['count' => 0, 'percentage' => 0, 'avg_score' => 0],
                'moderate' => ['count' => 0, 'percentage' => 0, 'avg_score' => 0],
                'at_risk' => ['count' => 0, 'percentage' => 0, 'avg_score' => 0],
                'high_risk' => ['count' => 0, 'percentage' => 0, 'avg_score' => 0],
            ];

            $total = $households->count();
            if ($total === 0) return $distribution;

            foreach ($distribution as $behavior => &$data) {
                $group = $households->where('payment_behavior', $behavior);
                $data['count'] = $group->count();
                $data['percentage'] = round(($data['count'] / $total) * 100, 1);
                $data['avg_score'] = $group->count() > 0
                    ? round($group->avg('payment_reliability_score'), 1)
                    : 0;
            }

            return $distribution;
        });
    }

    public function getSystemDistribution(): array
    {
        return $this->cached("reliability:distribution:system", function () {
            $households = Household::where('status', 'active')
                ->where('approval_status', 'approved')
                ->whereNotNull('payment_behavior')
                ->get();

            $distribution = [
                'excellent' => ['count' => 0, 'percentage' => 0, 'avg_score' => 0],
                'good' => ['count' => 0, 'percentage' => 0, 'avg_score' => 0],
                'moderate' => ['count' => 0, 'percentage' => 0, 'avg_score' => 0],
                'at_risk' => ['count' => 0, 'percentage' => 0, 'avg_score' => 0],
                'high_risk' => ['count' => 0, 'percentage' => 0, 'avg_score' => 0],
            ];

            $total = $households->count();
            if ($total === 0) return $distribution;

            foreach ($distribution as $behavior => &$data) {
                $group = $households->where('payment_behavior', $behavior);
                $data['count'] = $group->count();
                $data['percentage'] = round(($data['count'] / $total) * 100, 1);
                $data['avg_score'] = $group->count() > 0
                    ? round($group->avg('payment_reliability_score'), 1)
                    : 0;
            }

            return $distribution;
        });
    }

    public function getScoreDistribution(int $villageId): array
    {
        return $this->cached("reliability:score-dist:{$villageId}", function () use ($villageId) {
            $households = Household::where('village_id', $villageId)
                ->where('status', 'active')
                ->where('approval_status', 'approved')
                ->whereNotNull('payment_behavior')
                ->pluck('payment_reliability_score');

            if ($households->isEmpty()) {
                return ['ranges' => [], 'labels' => [], 'counts' => []];
            }

            $ranges = [
                ['label' => '0-20', 'min' => 0, 'max' => 20],
                ['label' => '21-40', 'min' => 21, 'max' => 40],
                ['label' => '41-60', 'min' => 41, 'max' => 60],
                ['label' => '61-80', 'min' => 61, 'max' => 80],
                ['label' => '81-100', 'min' => 81, 'max' => 100],
            ];

            $labels = [];
            $counts = [];
            foreach ($ranges as $range) {
                $labels[] = $range['label'];
                $counts[] = $households->filter(fn($s) => $s >= $range['min'] && $s <= $range['max'])->count();
            }

            return ['ranges' => $ranges, 'labels' => $labels, 'counts' => $counts];
        });
    }

    public function getVillageComparison(): array
    {
        return $this->cached("reliability:comparison:system", function () {
            $villages = Village::where('status', 'active')
                ->whereHas('paymentConfiguration', fn($q) => $q->where('payment_enabled', true))
                ->get();

            $comparison = [];
            foreach ($villages as $village) {
                $distribution = $this->getVillageDistribution($village->id);
                $households = Household::where('village_id', $village->id)
                    ->where('status', 'active')
                    ->where('approval_status', 'approved')
                    ->whereNotNull('payment_behavior')
                    ->get();

                $comparison[] = [
                    'village_id' => $village->id,
                    'village_name' => $village->village_name,
                    'village_code' => $village->village_code,
                    'total_households' => $households->count(),
                    'avg_score' => $households->count() > 0
                        ? round($households->avg('payment_reliability_score'), 1)
                        : 0,
                    'excellent' => $distribution['excellent']['count'],
                    'good' => $distribution['good']['count'],
                    'moderate' => $distribution['moderate']['count'],
                    'at_risk' => $distribution['at_risk']['count'],
                    'high_risk' => $distribution['high_risk']['count'],
                ];
            }

            return $comparison;
        });
    }

    public function getTopPerformers(int $villageId, int $limit = 10): \Illuminate\Support\Collection
    {
        return Household::where('village_id', $villageId)
            ->where('status', 'active')
            ->where('approval_status', 'approved')
            ->whereNotNull('payment_behavior')
            ->orderByDesc('payment_reliability_score')
            ->limit($limit)
            ->get();
    }

    public function getAtRiskHouseholds(int $villageId, int $limit = 10): \Illuminate\Support\Collection
    {
        return Household::where('village_id', $villageId)
            ->where('status', 'active')
            ->where('approval_status', 'approved')
            ->whereIn('payment_behavior', ['at_risk', 'high_risk'])
            ->orderBy('payment_reliability_score')
            ->limit($limit)
            ->get();
    }

    public function getAverageScore(int $villageId): float
    {
        return $this->cached("reliability:avg:{$villageId}", function () use ($villageId) {
            $avg = Household::where('village_id', $villageId)
                ->where('status', 'active')
                ->where('approval_status', 'approved')
                ->whereNotNull('payment_behavior')
                ->avg('payment_reliability_score');

            return round($avg ?? 0, 1);
        });
    }

    public function getSystemAverageScore(): float
    {
        return $this->cached("reliability:avg:system", function () {
            $avg = Household::where('status', 'active')
                ->where('approval_status', 'approved')
                ->whereNotNull('payment_behavior')
                ->avg('payment_reliability_score');

            return round($avg ?? 0, 1);
        });
    }
}
