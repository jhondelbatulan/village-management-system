<?php

namespace App\Services\Analytics;

use App\Models\Resident;
use App\Models\Village;
use Illuminate\Database\Eloquent\Builder;

class CommunityReadinessService
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

    public function getScore(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId);
        $total = $query->count();

        if ($total === 0) {
            return [
                'overall_score' => 0,
                'rating' => 'Needs Improvement',
                'breakdown' => [],
                'factors' => [],
            ];
        }

        $skilledWorkers = (clone $query)->where(function ($q) {
            $q->whereNotNull('primary_skill')
                ->where('primary_skill', '!=', '')
                ->orWhereNotNull('secondary_skill')
                ->where('secondary_skill', '!=', '');
        })->count();

        $philhealthMembers = (clone $query)->where('is_philhealth_member', 1)->count();

        $disasterVolunteers = (clone $query)->where('is_disaster_volunteer', 1)->count();
        $villageVolunteers = (clone $query)->where('is_village_volunteer', 1)->count();
        $totalVolunteers = $disasterVolunteers + $villageVolunteers;

        $communityParticipants = (clone $query)->whereNotNull('community_activities')
            ->where('community_activities', '!=', '')
            ->count();

        $governmentMembers = (clone $query)->where(function ($q) {
            $q->where('is_pagibig_member', 1)
                ->orWhere('is_sss_member', 1)
                ->orWhere('is_gsis_member', 1)
                ->orWhere('is_national_id_holder', 1)
                ->orWhere('is_senior_id_holder', 1)
                ->orWhere('is_pwd_id_holder', 1)
                ->orWhere('is_solo_parent_id_holder', 1);
        })->count();

        $skilledScore = $this->stats->percentage($skilledWorkers, $total);
        $healthcareScore = $this->stats->percentage($philhealthMembers, $total);
        $volunteerScore = $this->stats->percentage($totalVolunteers, $total);
        $participationScore = $this->stats->percentage($communityParticipants, $total);
        $organizationScore = $this->stats->percentage($governmentMembers, $total);

        $breakdown = [
            'skilled_workers' => [
                'count' => $skilledWorkers,
                'total' => $total,
                'score' => round($skilledScore),
                'percentage' => $skilledScore,
            ],
            'healthcare_coverage' => [
                'count' => $philhealthMembers,
                'total' => $total,
                'score' => round($healthcareScore),
                'percentage' => $healthcareScore,
            ],
            'volunteer_availability' => [
                'count' => $totalVolunteers,
                'disaster_volunteers' => $disasterVolunteers,
                'village_volunteers' => $villageVolunteers,
                'total' => $total,
                'score' => round($volunteerScore),
                'percentage' => $volunteerScore,
            ],
            'community_participation' => [
                'count' => $communityParticipants,
                'total' => $total,
                'score' => round($participationScore),
                'percentage' => $participationScore,
            ],
            'organization_membership' => [
                'count' => $governmentMembers,
                'total' => $total,
                'score' => round($organizationScore),
                'percentage' => $organizationScore,
            ],
        ];

        $overallScore = round(($skilledScore + $healthcareScore + $volunteerScore + $participationScore + $organizationScore) / 5);

        if ($overallScore >= 80) {
            $rating = 'Excellent';
        } elseif ($overallScore >= 60) {
            $rating = 'Prepared';
        } elseif ($overallScore >= 40) {
            $rating = 'Moderately Prepared';
        } else {
            $rating = 'Needs Improvement';
        }

        $factors = $this->getFactors($breakdown);

        return [
            'overall_score' => $overallScore,
            'rating' => $rating,
            'breakdown' => $breakdown,
            'factors' => $factors,
        ];
    }

    protected function getFactors(array $breakdown): array
    {
        $factors = [];

        foreach ($breakdown as $key => $data) {
            $factors[$key] = [
                'score' => $data['score'],
                'impact' => $data['score'] >= 60 ? 'positive' : 'needs_attention',
                'label' => $this->getFactorLabel($key),
            ];
        }

        return $factors;
    }

    protected function getFactorLabel(string $key): string
    {
        return match ($key) {
            'skilled_workers' => 'Skilled Worker Availability',
            'healthcare_coverage' => 'Healthcare Coverage',
            'volunteer_availability' => 'Volunteer Availability',
            'community_participation' => 'Community Participation',
            'organization_membership' => 'Government Organization Membership',
            default => ucfirst(str_replace('_', ' ', $key)),
        };
    }

    public function getAllVillageScores(): array
    {
        $villages = Village::where('status', 'active')->get();
        $scores = [];

        foreach ($villages as $village) {
            $scores[$village->id] = $this->getScore($village->id);
            $scores[$village->id]['village'] = [
                'id' => $village->id,
                'name' => $village->village_name,
                'code' => $village->village_code,
            ];
        }

        return $scores;
    }

    public function getVillageRankings(): array
    {
        $allScores = $this->getAllVillageScores();
        $rankings = [];

        foreach ($allScores as $villageId => $data) {
            $rankings[] = [
                'village_id' => $villageId,
                'village_name' => $data['village']['name'],
                'village_code' => $data['village']['code'],
                'overall_score' => $data['overall_score'],
                'rating' => $data['rating'],
            ];
        }

        usort($rankings, fn($a, $b) => $b['overall_score'] <=> $a['overall_score']);

        foreach ($rankings as $index => &$rank) {
            $rank['rank'] = $index + 1;
        }

        return $rankings;
    }

    public function getInsights(?int $villageId = null): array
    {
        $score = $this->getScore($villageId);
        $insights = [];
        $breakdown = $score['breakdown'];

        $insights[] = [
            'type' => 'overall',
            'message' => "Overall readiness score: {$score['overall_score']}/100 ({$score['rating']}).",
        ];

        $sorted = collect($breakdown)->sortBy('score');
        $weakest = $sorted->first();
        $strongest = $sorted->last();

        if ($weakest) {
            $weakestKey = $sorted->keys()->first();
            $insights[] = [
                'type' => 'warning',
                'message' => "Weakest area: {$this->getFactorLabel($weakestKey)} at {$weakest['score']}%.",
            ];
        }

        if ($strongest) {
            $strongestKey = $sorted->keys()->last();
            $insights[] = [
                'type' => 'success',
                'message' => "Strongest area: {$this->getFactorLabel($strongestKey)} at {$strongest['score']}%.",
            ];
        }

        if ($score['overall_score'] < 40) {
            $insights[] = [
                'type' => 'critical',
                'message' => 'Community readiness is critically low. Urgent intervention recommended.',
            ];
        } elseif ($score['overall_score'] >= 80) {
            $insights[] = [
                'type' => 'success',
                'message' => 'Community is well-prepared for disaster response and engagement.',
            ];
        }

        if (!$villageId) {
            $rankings = $this->getVillageRankings();
            if (!empty($rankings)) {
                $top = $rankings[0];
                $bottom = end($rankings);
                $insights[] = [
                    'type' => 'info',
                    'message' => "Top-ranked village: {$top['village_name']} ({$top['overall_score']}%). Lowest: {$bottom['village_name']} ({$bottom['overall_score']}%).",
                ];
            }
        }

        return $insights;
    }
}
