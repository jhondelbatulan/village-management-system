<?php

namespace App\Services\Analytics;

use App\Models\Household;

class PaymentRecommendationService
{
    public const RECOMMENDATIONS = [
        'excellent' => [
            'action' => 'Continue current payment schedule',
            'priority' => 'low',
            'color' => 'success',
            'icon' => 'bi-check-circle',
            'details' => [
                'Continue current payment schedule',
                'No action required at this time',
                'Household has excellent payment track record',
            ],
        ],
        'good' => [
            'action' => 'Send regular reminders',
            'priority' => 'low',
            'color' => 'primary',
            'icon' => 'bi-bell',
            'details' => [
                'Send regular payment reminders before due date',
                'Maintain current payment relationship',
                'Monitor for any changes in payment behavior',
            ],
        ],
        'moderate' => [
            'action' => 'Send reminder several days before due date',
            'priority' => 'medium',
            'color' => 'warning',
            'icon' => 'bi-exclamation-circle',
            'details' => [
                'Send reminder 5-7 days before due date',
                'Follow up on partial payments',
                'Review payment plan options if needed',
                'Consider flexible payment arrangements',
            ],
        ],
        'at_risk' => [
            'action' => 'Recommend Treasurer follow-up',
            'priority' => 'high',
            'color' => 'danger',
            'icon' => 'bi-exclamation-triangle',
            'details' => [
                'Recommend Treasurer follow-up call',
                'Send personalized payment reminder',
                'Discuss payment arrangement options',
                'Monitor payment closely this month',
                'Consider home visit if no response',
            ],
        ],
        'high_risk' => [
            'action' => 'Recommend personal visit or payment arrangement',
            'priority' => 'urgent',
            'color' => 'danger',
            'icon' => 'bi-x-circle',
            'details' => [
                'Recommend personal visit by Treasurer or Barangay official',
                'Create formal payment arrangement plan',
                'Assess household financial situation',
                'Consider community assistance programs',
                'Schedule regular follow-up meetings',
                'Document all interactions and agreements',
            ],
        ],
    ];

    public function getRecommendation(Household $household): array
    {
        $behavior = $household->payment_behavior ?? 'moderate';

        $recommendation = self::RECOMMENDATIONS[$behavior] ?? self::RECOMMENDATIONS['moderate'];

        $recommendation['household_id'] = $household->id;
        $recommendation['household_number'] = $household->household_number;
        $recommendation['head_name'] = $household->household_head_name;
        $recommendation['behavior'] = $behavior;
        $recommendation['score'] = $household->payment_reliability_score ?? 50;
        $recommendation['confidence'] = $household->prediction_confidence ?? 0;

        return $recommendation;
    }

    public function getVillageRecommendations(int $villageId): array
    {
        $households = \App\Models\Household::where('village_id', $villageId)
            ->where('status', 'active')
            ->where('approval_status', 'approved')
            ->whereNotNull('payment_behavior')
            ->orderBy('payment_reliability_score')
            ->get();

        $recommendations = [];
        foreach ($households as $household) {
            $rec = $this->getRecommendation($household);
            $rec['village_id'] = $villageId;
            $recommendations[] = $rec;
        }

        return $recommendations;
    }

    public function getHighPriorityRecommendations(int $villageId): array
    {
        return array_filter(
            $this->getVillageRecommendations($villageId),
            fn($r) => in_array($r['priority'], ['high', 'urgent'])
        );
    }

    public function getRecommendationSummary(int $villageId): array
    {
        $households = \App\Models\Household::where('village_id', $villageId)
            ->where('status', 'active')
            ->where('approval_status', 'approved')
            ->whereNotNull('payment_behavior')
            ->get();

        $summary = [
            'total' => $households->count(),
            'no_action' => $households->where('payment_behavior', 'excellent')->count(),
            'send_reminders' => $households->where('payment_behavior', 'good')->count(),
            'follow_up_needed' => $households->where('payment_behavior', 'moderate')->count(),
            'treasurer_action' => $households->where('payment_behavior', 'at_risk')->count(),
            'personal_visit' => $households->where('payment_behavior', 'high_risk')->count(),
        ];

        return $summary;
    }

    public function getRecommendationLabel(string $behavior): string
    {
        return self::RECOMMENDATIONS[$behavior]['action'] ?? 'Review payment status';
    }

    public function getRecommendationColor(string $behavior): string
    {
        return self::RECOMMENDATIONS[$behavior]['color'] ?? 'secondary';
    }

    public function getPriorityLabel(string $priority): string
    {
        return match($priority) {
            'urgent' => 'Urgent',
            'high' => 'High Priority',
            'medium' => 'Medium Priority',
            'low' => 'Low Priority',
            default => 'Normal',
        };
    }

    public function getPriorityColor(string $priority): string
    {
        return match($priority) {
            'urgent' => 'danger',
            'high' => 'warning',
            'medium' => 'info',
            'low' => 'success',
            default => 'secondary',
        };
    }
}
