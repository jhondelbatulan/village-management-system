<?php

namespace App\Services\Analytics;

use App\Models\Household;
use App\Models\HouseholdPayment;
use App\Models\Billing;
use App\Models\Village;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PredictionAnalyticsService extends StatisticsService
{
    public function __construct(
        protected PaymentBehaviorService $behaviorService,
        protected PaymentReliabilityService $reliabilityService,
        protected PaymentRecommendationService $recommendationService
    ) {}

    public function getVillageStats(int $villageId): array
    {
        return $this->cached("prediction:stats:{$villageId}", function () use ($villageId) {
            $households = Household::where('village_id', $villageId)
                ->where('status', 'active')
                ->where('approval_status', 'approved')
                ->whereNotNull('payment_behavior')
                ->get();

            $total = $households->count();
            if ($total === 0) {
                return $this->getEmptyStats();
            }

            $distribution = $this->reliabilityService->getVillageDistribution($villageId);
            $avgScore = $households->avg('payment_reliability_score');

            $payments = HouseholdPayment::where('village_id', $villageId)
                ->where('payment_status', '!=', 'cancelled')
                ->get();

            $billings = Billing::where('village_id', $villageId)
                ->where('status', '!=', 'cancelled')
                ->get();

            $totalCollected = $payments->where('payment_status', 'paid')->sum('total_paid');
            $pendingBillings = $billings->filter(fn($b) => in_array($b->status, ['pending', 'overdue', 'partially_paid']));
            $totalOutstanding = 0;
            foreach ($pendingBillings as $b) {
                $paid = HouseholdPayment::where('billing_id', $b->id)
                    ->where('payment_status', '!=', 'cancelled')
                    ->sum('total_paid');
                $totalOutstanding += max(0, $b->total_amount - $paid);
            }

            $onTimePayments = $payments->filter(fn($p) => $p->payment_date && $p->due_date && $p->payment_date <= $p->due_date && $p->payment_status === 'paid')->count();
            $totalPaidPayments = $payments->where('payment_status', 'paid')->count();
            $latePayments = $payments->filter(fn($p) => $p->payment_date && $p->due_date && $p->payment_date > $p->due_date && $p->payment_status === 'paid')->count();
            $partialPayments = $payments->where('payment_status', 'partially_paid')->count();
            $totalPaymentCount = $payments->count();

            $onTimeRate = $totalPaymentCount > 0 ? round(($onTimePayments / $totalPaymentCount) * 100, 1) : 0;
            $lateRate = $totalPaymentCount > 0 ? round(($latePayments / $totalPaymentCount) * 100, 1) : 0;
            $partialRate = $totalPaymentCount > 0 ? round(($partialPayments / $totalPaymentCount) * 100, 1) : 0;

            $collectionRate = $billings->count() > 0
                ? round(($billings->where('status', 'paid')->count() / $billings->count()) * 100, 1)
                : 0;

            $avgDelay = $this->calculateAverageDelay($payments);

            return [
                'total_households' => $total,
                'avg_score' => round($avgScore ?? 0, 1),
                'distribution' => $distribution,
                'collection_rate' => $collectionRate,
                'on_time_rate' => $onTimeRate,
                'late_rate' => $lateRate,
                'partial_rate' => $partialRate,
                'total_collected' => round($totalCollected, 2),
                'total_outstanding' => round($totalOutstanding, 2),
                'avg_delay_days' => $avgDelay,
                'high_risk_count' => $distribution['high_risk']['count'],
                'at_risk_count' => $distribution['at_risk']['count'],
            ];
        });
    }

    public function getSystemStats(): array
    {
        return $this->cached("prediction:stats:system", function () {
            $villages = Village::where('status', 'active')
                ->whereHas('paymentConfiguration', fn($q) => $q->where('payment_enabled', true))
                ->get();

            $allStats = [];
            foreach ($villages as $village) {
                $allStats[] = $this->getVillageStats($village->id);
            }

            if (empty($allStats)) {
                return $this->getEmptyStats();
            }

            $totalHouseholds = array_sum(array_column($allStats, 'total_households'));
            $avgScore = $totalHouseholds > 0
                ? round(array_sum(array_map(fn($s) => $s['avg_score'] * $s['total_households'], $allStats)) / $totalHouseholds, 1)
                : 0;

            $totalCollected = array_sum(array_column($allStats, 'total_collected'));
            $totalOutstanding = array_sum(array_column($allStats, 'total_outstanding'));

            $behaviors = ['excellent', 'good', 'moderate', 'at_risk', 'high_risk'];
            $distribution = [];
            foreach ($behaviors as $b) {
                $totalCount = 0;
                $totalScore = 0;
                foreach ($allStats as $s) {
                    $count = $s['distribution'][$b]['count'] ?? 0;
                    $totalCount += $count;
                    $totalScore += ($s['distribution'][$b]['avg_score'] ?? 0) * $count;
                }
                $distribution[$b] = [
                    'count' => $totalCount,
                    'percentage' => $totalHouseholds > 0 ? round(($totalCount / $totalHouseholds) * 100, 1) : 0,
                    'avg_score' => $totalCount > 0 ? round($totalScore / $totalCount, 1) : 0,
                ];
            }

            $avgDelay = 0;
            $delaySum = 0;
            $delayCount = 0;
            foreach ($allStats as $s) {
                if (isset($s['avg_delay_days']) && $s['total_households'] > 0) {
                    $delaySum += $s['avg_delay_days'] * $s['total_households'];
                    $delayCount += $s['total_households'];
                }
            }
            $avgDelay = $delayCount > 0 ? round($delaySum / $delayCount, 1) : 0;

            return [
                'total_households' => $totalHouseholds,
                'avg_score' => $avgScore,
                'distribution' => $distribution,
                'collection_rate' => $totalHouseholds > 0
                    ? round(array_sum(array_column($allStats, 'collection_rate')) / count($allStats), 1)
                    : 0,
                'on_time_rate' => $totalHouseholds > 0
                    ? round(array_sum(array_column($allStats, 'on_time_rate')) / count($allStats), 1)
                    : 0,
                'late_rate' => $totalHouseholds > 0
                    ? round(array_sum(array_column($allStats, 'late_rate')) / count($allStats), 1)
                    : 0,
                'partial_rate' => $totalHouseholds > 0
                    ? round(array_sum(array_column($allStats, 'partial_rate')) / count($allStats), 1)
                    : 0,
                'total_collected' => $totalCollected,
                'total_outstanding' => $totalOutstanding,
                'avg_delay_days' => $avgDelay,
                'high_risk_count' => array_sum(array_column($allStats, 'high_risk_count')),
                'at_risk_count' => array_sum(array_column($allStats, 'at_risk_count')),
            ];
        });
    }

    public function getInsights(int $villageId): array
    {
        return $this->cached("prediction:insights:{$villageId}", function () use ($villageId) {
            $stats = $this->getVillageStats($villageId);
            $insights = [];

            if ($stats['total_households'] === 0) {
                return [['type' => 'info', 'message' => 'No payment data available for prediction analysis.']];
            }

            if ($stats['collection_rate'] >= 80) {
                $insights[] = ['type' => 'success', 'message' => "Collection rate is strong at {$stats['collection_rate']}%. Most households pay on time."];
            } elseif ($stats['collection_rate'] >= 60) {
                $insights[] = ['type' => 'warning', 'message' => "Collection rate is {$stats['collection_rate']}%. Some households need payment follow-up."];
            } else {
                $insights[] = ['type' => 'danger', 'message' => "Collection rate is low at {$stats['collection_rate']}%. Immediate action needed."];
            }

            $highRisk = $stats['distribution']['high_risk']['count'] ?? 0;
            if ($highRisk > 0) {
                $insights[] = ['type' => 'danger', 'message' => "{$highRisk} household" . ($highRisk > 1 ? 's have' : ' has') . " a high probability of delayed payments. Recommend personal follow-up."];
            }

            $atRisk = $stats['distribution']['at_risk']['count'] ?? 0;
            if ($atRisk > 0) {
                $insights[] = ['type' => 'warning', 'message' => "{$atRisk} household" . ($atRisk > 1 ? 's are' : ' is') . " at risk of falling behind. Treasurer should monitor closely."];
            }

            $excellent = $stats['distribution']['excellent']['count'] ?? 0;
            $good = $stats['distribution']['good']['count'] ?? 0;
            $reliable = $excellent + $good;
            if ($reliable > 0) {
                $insights[] = ['type' => 'success', 'message' => "{$reliable} household" . ($reliable > 1 ? 's have' : ' has') . " excellent or good payment behavior. Keep up the good work!"];
            }

            if ($stats['on_time_rate'] >= 70) {
                $insights[] = ['type' => 'info', 'message' => "Most households consistently pay before the due date ({$stats['on_time_rate']}% on-time rate)."];
            }

            if ($stats['late_rate'] > 20) {
                $insights[] = ['type' => 'warning', 'message' => "{$stats['late_rate']}% of payments are late. Consider sending earlier payment reminders."];
            }

            if ($stats['partial_rate'] > 15) {
                $insights[] = ['type' => 'info', 'message' => "{$stats['partial_rate']}% of payments are partial. Consider flexible payment arrangements."];
            }

            return $insights;
        });
    }

    public function getMonthlyTrend(int $villageId, int $months = 12): array
    {
        return $this->cached("prediction:trend:{$villageId}:{$months}", function () use ($villageId, $months) {
            $trend = [];
            $now = Carbon::now();

            for ($i = $months - 1; $i >= 0; $i--) {
                $month = $now->copy()->subMonths($i);
                $monthStart = $month->copy()->startOfMonth();
                $monthEnd = $month->copy()->endOfMonth();

                $payments = HouseholdPayment::where('village_id', $villageId)
                    ->where('payment_status', '!=', 'cancelled')
                    ->where('payment_date', '>=', $monthStart->format('Y-m-d'))
                    ->where('payment_date', '<=', $monthEnd->format('Y-m-d'))
                    ->get();

                $billings = Billing::where('village_id', $villageId)
                    ->where('status', '!=', 'cancelled')
                    ->where('due_date', '>=', $monthStart->format('Y-m-d'))
                    ->where('due_date', '<=', $monthEnd->format('Y-m-d'))
                    ->get();

                $totalPayments = $payments->count();
                $onTime = $payments->filter(fn($p) => $p->payment_date && $p->due_date && $p->payment_date <= $p->due_date && $p->payment_status === 'paid')->count();
                $late = $payments->filter(fn($p) => $p->payment_date && $p->due_date && $p->payment_date > $p->due_date && $p->payment_status === 'paid')->count();
                $partial = $payments->where('payment_status', 'partially_paid')->count();
                $collected = $payments->where('payment_status', 'paid')->sum('total_paid');

                $trend[] = [
                    'month' => $month->format('M Y'),
                    'month_short' => $month->format('M'),
                    'total_payments' => $totalPayments,
                    'on_time' => $onTime,
                    'late' => $late,
                    'partial' => $partial,
                    'collected' => round($collected, 2),
                    'collection_rate' => $billings->count() > 0
                        ? round(($billings->where('status', 'paid')->count() / $billings->count()) * 100, 1)
                        : 0,
                    'on_time_rate' => $totalPayments > 0
                        ? round(($onTime / $totalPayments) * 100, 1)
                        : 0,
                ];
            }

            return $trend;
        });
    }

    public function getConsistencyTrend(int $villageId, int $months = 12): array
    {
        return $this->cached("prediction:consistency:{$villageId}:{$months}", function () use ($villageId, $months) {
            $trend = $this->getMonthlyTrend($villageId, $months);

            return array_map(fn($m) => [
                'month' => $m['month_short'],
                'on_time_rate' => $m['on_time_rate'],
                'late_rate' => $m['total_payments'] > 0 ? round(($m['late'] / $m['total_payments']) * 100, 1) : 0,
                'partial_rate' => $m['total_payments'] > 0 ? round(($m['partial'] / $m['total_payments']) * 100, 1) : 0,
            ], $trend);
        });
    }

    protected function calculateAverageDelay($payments): float
    {
        $delays = $payments->filter(fn($p) => $p->payment_date && $p->due_date && $p->payment_date > $p->due_date)
            ->map(fn($p) => Carbon::parse($p->due_date)->diffInDays(Carbon::parse($p->payment_date)));

        return $delays->count() > 0 ? round($delays->avg(), 1) : 0;
    }

    protected function getEmptyStats(): array
    {
        return [
            'total_households' => 0,
            'avg_score' => 0,
            'distribution' => [
                'excellent' => ['count' => 0, 'percentage' => 0, 'avg_score' => 0],
                'good' => ['count' => 0, 'percentage' => 0, 'avg_score' => 0],
                'moderate' => ['count' => 0, 'percentage' => 0, 'avg_score' => 0],
                'at_risk' => ['count' => 0, 'percentage' => 0, 'avg_score' => 0],
                'high_risk' => ['count' => 0, 'percentage' => 0, 'avg_score' => 0],
            ],
            'collection_rate' => 0,
            'on_time_rate' => 0,
            'late_rate' => 0,
            'partial_rate' => 0,
            'total_collected' => 0,
            'total_outstanding' => 0,
            'avg_delay_days' => 0,
            'high_risk_count' => 0,
            'at_risk_count' => 0,
        ];
    }
}
