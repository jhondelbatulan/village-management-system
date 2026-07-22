<?php

namespace App\Services\Analytics;

use App\Models\Household;
use App\Models\HouseholdPayment;
use App\Models\Billing;
use App\Models\Village;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentBehaviorService extends StatisticsService
{
    public const BEHAVIOR_EXCELLENT = 'excellent';
    public const BEHAVIOR_GOOD = 'good';
    public const BEHAVIOR_MODERATE = 'moderate';
    public const BEHAVIOR_AT_RISK = 'at_risk';
    public const BEHAVIOR_HIGH_RISK = 'high_risk';

    public const BEHAVIOR_LABELS = [
        self::BEHAVIOR_EXCELLENT => 'Excellent Payer',
        self::BEHAVIOR_GOOD => 'Good Payer',
        self::BEHAVIOR_MODERATE => 'Moderate Payer',
        self::BEHAVIOR_AT_RISK => 'At-Risk Payer',
        self::BEHAVIOR_HIGH_RISK => 'High-Risk Payer',
    ];

    public const BEHAVIOR_COLORS = [
        self::BEHAVIOR_EXCELLENT => 'success',
        self::BEHAVIOR_GOOD => 'primary',
        self::BEHAVIOR_MODERATE => 'warning',
        self::BEHAVIOR_AT_RISK => 'danger',
        self::BEHAVIOR_HIGH_RISK => 'danger',
    ];

    public const BEHAVIOR_ICONS = [
        self::BEHAVIOR_EXCELLENT => 'bi-check-circle-fill',
        self::BEHAVIOR_GOOD => 'bi-check-circle',
        self::BEHAVIOR_MODERATE => 'bi-exclamation-circle',
        self::BEHAVIOR_AT_RISK => 'bi-exclamation-triangle',
        self::BEHAVIOR_HIGH_RISK => 'bi-x-circle-fill',
    ];

    public const SCORING_WEIGHTS = [
        'on_time_bonus' => 15,
        'late_bonus' => 8,
        'missed_penalty' => 20,
        'partial_bonus' => 5,
        'consecutive_on_time_bonus' => 3,
        'consecutive_missed_penalty' => 5,
        'completion_weight' => 30,
        'consistency_weight' => 20,
        'recency_weight' => 10,
    ];

    public function calculateScore(Household $household): array
    {
        $villageId = $household->village_id;

        if (!$this->isPaymentEnabled($villageId)) {
            return $this->getDefaultResult();
        }

        $payments = HouseholdPayment::where('household_id', $household->id)
            ->where('village_id', $villageId)
            ->where('payment_status', '!=', 'cancelled')
            ->orderBy('payment_date')
            ->get();

        $billings = Billing::where('household_id', $household->id)
            ->where('village_id', $villageId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('due_date')
            ->get();

        if ($payments->isEmpty() && $billings->isEmpty()) {
            return $this->getDefaultResult();
        }

        $totalBillings = $billings->count();
        $totalPayments = $payments->count();

        $onTimePayments = $payments->filter(fn($p) => $p->payment_date && $p->due_date && $p->payment_date <= $p->due_date)->count();
        $latePayments = $payments->filter(fn($p) => $p->payment_date && $p->due_date && $p->payment_date > $p->due_date && in_array($p->payment_status, ['paid']))->count();
        $missedPayments = $billings->filter(fn($b) => in_array($b->status, ['overdue']) || ($b->status === 'pending' && Carbon::parse($b->due_date)->isPast()))->count();
        $partialPayments = $payments->filter(fn($p) => $p->payment_status === 'partially_paid')->count();

        $paidBillings = $billings->filter(fn($b) => $b->status === 'paid')->count();
        $completionPercentage = $totalBillings > 0 ? ($paidBillings / $totalBillings) * 100 : 0;

        $consecutiveOnTime = $this->getConsecutiveOnTime($payments);
        $consecutiveMissed = $this->getConsecutiveMissed($billings);

        $consistencyScore = $this->calculateConsistency($payments, $billings);

        $score = 50.0;
        $reasons = [];

        if ($totalBillings > 0) {
            $score += ($onTimePayments * self::SCORING_WEIGHTS['on_time_bonus']);
            if ($onTimePayments > 0) {
                $reasons[] = "{$onTimePayments} on-time payment" . ($onTimePayments > 1 ? 's' : '');
            }

            $score -= ($latePayments * self::SCORING_WEIGHTS['late_bonus']);
            if ($latePayments > 0) {
                $reasons[] = "{$latePayments} late payment" . ($latePayments > 1 ? 's' : '');
            }

            $score -= ($missedPayments * self::SCORING_WEIGHTS['missed_penalty']);
            if ($missedPayments > 0) {
                $reasons[] = "{$missedPayments} missed/overdue payment" . ($missedPayments > 1 ? 's' : '');
            }

            $score += ($partialPayments * self::SCORING_WEIGHTS['partial_bonus']);
            if ($partialPayments > 0) {
                $reasons[] = "{$partialPayments} partial payment" . ($partialPayments > 1 ? 's' : '');
            }
        }

        if ($consecutiveOnTime >= 3) {
            $bonus = $consecutiveOnTime * self::SCORING_WEIGHTS['consecutive_on_time_bonus'];
            $score += $bonus;
            $reasons[] = "{$consecutiveOnTime} consecutive on-time payments";
        }

        if ($consecutiveMissed >= 2) {
            $penalty = $consecutiveMissed * self::SCORING_WEIGHTS['consecutive_missed_penalty'];
            $score -= $penalty;
            $reasons[] = "{$consecutiveMissed} consecutive missed payments";
        }

        $completionBonus = ($completionPercentage / 100) * self::SCORING_WEIGHTS['completion_weight'];
        $score += $completionBonus;

        $consistencyBonus = ($consistencyScore / 100) * self::SCORING_WEIGHTS['consistency_weight'];
        $score += $consistencyBonus;

        $recencyBonus = $this->calculateRecencyBonus($payments) * self::SCORING_WEIGHTS['recency_weight'];
        $score += $recencyBonus;

        $score = max(0, min(100, $score));

        $behavior = $this->classifyBehavior($score);

        $confidence = $this->calculateConfidence($totalBillings, $totalPayments);

        if (empty($reasons)) {
            $reasons[] = 'New household with limited payment history';
        }

        return [
            'score' => round($score, 2),
            'behavior' => $behavior,
            'behavior_label' => self::BEHAVIOR_LABELS[$behavior],
            'behavior_color' => self::BEHAVIOR_COLORS[$behavior],
            'behavior_icon' => self::BEHAVIOR_ICONS[$behavior],
            'confidence' => round($confidence, 2),
            'reasons' => $reasons,
            'stats' => [
                'total_billings' => $totalBillings,
                'total_payments' => $totalPayments,
                'on_time' => $onTimePayments,
                'late' => $latePayments,
                'missed' => $missedPayments,
                'partial' => $partialPayments,
                'completion_percentage' => round($completionPercentage, 2),
                'consecutive_on_time' => $consecutiveOnTime,
                'consecutive_missed' => $consecutiveMissed,
                'consistency_score' => round($consistencyScore, 2),
            ],
        ];
    }

    public function recalculateHousehold(Household $household): void
    {
        $result = $this->calculateScore($household);

        $household->update([
            'payment_behavior' => $result['behavior'],
            'payment_reliability_score' => $result['score'],
            'prediction_confidence' => $result['confidence'],
            'prediction_reasons' => json_encode($result['reasons']),
            'last_prediction_at' => Carbon::now(),
        ]);
    }

    public function recalculateVillage(int $villageId): void
    {
        $households = Household::where('village_id', $villageId)
            ->where('status', 'active')
            ->where('approval_status', 'approved')
            ->get();

        foreach ($households as $household) {
            $this->recalculateHousehold($household);
        }
    }

    public function recalculateAll(): void
    {
        $villages = Village::whereHas('paymentConfiguration', fn($q) => $q->where('payment_enabled', true))
            ->pluck('id');

        foreach ($villages as $villageId) {
            $this->recalculateVillage($villageId);
        }
    }

    protected function getConsecutiveOnTime($payments): int
    {
        $sorted = $payments->sortByDesc('payment_date')->values();
        $count = 0;

        foreach ($sorted as $payment) {
            if ($payment->payment_date && $payment->due_date && $payment->payment_date <= $payment->due_date && $payment->payment_status === 'paid') {
                $count++;
            } else {
                break;
            }
        }

        return $count;
    }

    protected function getConsecutiveMissed($billings): int
    {
        $unpaid = $billings->filter(fn($b) => in_array($b->status, ['overdue', 'pending']))
            ->sortByDesc('due_date')
            ->values();

        $count = 0;
        foreach ($unpaid as $billing) {
            if ($billing->status === 'overdue' || ($billing->status === 'pending' && Carbon::parse($billing->due_date)->isPast())) {
                $count++;
            } else {
                break;
            }
        }

        return $count;
    }

    protected function calculateConsistency($payments, $billings): float
    {
        if ($billings->isEmpty()) return 0;

        $paidOnTime = $payments->filter(fn($p) => $p->payment_date && $p->due_date && $p->payment_date <= $p->due_date && $p->payment_status === 'paid')->count();
        $totalExpected = $billings->count();

        if ($totalExpected == 0) return 0;

        return ($paidOnTime / $totalExpected) * 100;
    }

    protected function calculateRecencyBonus($payments): float
    {
        $latest = $payments->sortByDesc('payment_date')->first();

        if (!$latest || !$latest->payment_date) return 0;

        $daysSinceLast = Carbon::now()->diffInDays(Carbon::parse($latest->payment_date));

        if ($latest->payment_status === 'paid') {
            if ($daysSinceLast <= 30) return 1.0;
            if ($daysSinceLast <= 60) return 0.7;
            if ($daysSinceLast <= 90) return 0.4;
            return 0.2;
        }

        return 0;
    }

    protected function calculateConfidence(int $totalBillings, int $totalPayments): float
    {
        if ($totalBillings == 0) return 10.0;

        $dataPoints = $totalBillings + $totalPayments;
        $confidence = min(95.0, 10.0 + ($dataPoints * 2.5));

        return round($confidence, 2);
    }

    protected function classifyBehavior(float $score): string
    {
        if ($score >= 85) return self::BEHAVIOR_EXCELLENT;
        if ($score >= 70) return self::BEHAVIOR_GOOD;
        if ($score >= 50) return self::BEHAVIOR_MODERATE;
        if ($score >= 30) return self::BEHAVIOR_AT_RISK;
        return self::BEHAVIOR_HIGH_RISK;
    }

    protected function getDefaultResult(): array
    {
        return [
            'score' => 50.00,
            'behavior' => self::BEHAVIOR_MODERATE,
            'behavior_label' => self::BEHAVIOR_LABELS[self::BEHAVIOR_MODERATE],
            'behavior_color' => self::BEHAVIOR_COLORS[self::BEHAVIOR_MODERATE],
            'behavior_icon' => self::BEHAVIOR_ICONS[self::BEHAVIOR_MODERATE],
            'confidence' => 10.00,
            'reasons' => ['New household with limited payment history'],
            'stats' => [
                'total_billings' => 0,
                'total_payments' => 0,
                'on_time' => 0,
                'late' => 0,
                'missed' => 0,
                'partial' => 0,
                'completion_percentage' => 0,
                'consecutive_on_time' => 0,
                'consecutive_missed' => 0,
                'consistency_score' => 0,
            ],
        ];
    }

    public function isPaymentEnabled(int $villageId): bool
    {
        return \App\Models\PaymentConfiguration::where('village_id', $villageId)
            ->where('payment_enabled', true)
            ->exists();
    }

    public function getBehaviorLabel(string $behavior): string
    {
        return self::BEHAVIOR_LABELS[$behavior] ?? 'Unknown';
    }

    public function getBehaviorColor(string $behavior): string
    {
        return self::BEHAVIOR_COLORS[$behavior] ?? 'secondary';
    }

    public function getBehaviorIcon(string $behavior): string
    {
        return self::BEHAVIOR_ICONS[$behavior] ?? 'bi-question-circle';
    }

    public function getScoreColor(float $score): string
    {
        if ($score >= 85) return 'success';
        if ($score >= 70) return 'primary';
        if ($score >= 50) return 'warning';
        if ($score >= 30) return 'danger';
        return 'danger';
    }
}
