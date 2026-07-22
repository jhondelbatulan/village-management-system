<?php

namespace App\Services\Analytics;

use App\Models\Household;
use App\Models\Resident;
use App\Models\Village;
use Illuminate\Database\Eloquent\Builder;

class SocioEconomicHealthScoreService
{
    protected array $components = [
        'employment',
        'education',
        'household_stability',
        'income_distribution',
        'government_membership',
        'healthcare_coverage',
        'skills_availability',
        'volunteer_participation',
        'community_organizations',
    ];

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
        $total = (clone $query)->count();

        if ($total === 0) {
            return $this->emptyScore();
        }

        $breakdown = [];

        $withEmploymentStatus = (clone $query)->whereNotNull('employment_status')
            ->where('employment_status', '!=', '')->count();
        $employed = (clone $query)->where('employment_status', 'Employed')->count();
        $breakdown['employment'] = $this->stats->percentage($employed, $withEmploymentStatus);

        $withEducation = (clone $query)->whereNotNull('educational_attainment')
            ->where('educational_attainment', '!=', '')->count();
        $breakdown['education'] = $this->stats->percentage($withEducation, $total);

        $breakdown['household_stability'] = $this->calculateHouseholdStability($villageId);

        $breakdown['income_distribution'] = $this->calculateIncomeDistribution($villageId);

        $withGovMembership = (clone $query)->where(function ($q) {
            $q->where('is_pagibig_member', 1)
                ->orWhere('is_philhealth_member', 1)
                ->orWhere('is_sss_member', 1)
                ->orWhere('is_gsis_member', 1)
                ->orWhere('is_national_id_holder', 1);
        })->count();
        $breakdown['government_membership'] = $this->stats->percentage($withGovMembership, $total);

        $philhealthMembers = (clone $query)->where('is_philhealth_member', 1)->count();
        $breakdown['healthcare_coverage'] = $this->stats->percentage($philhealthMembers, $total);

        $withSkills = (clone $query)->where(function ($q) {
            $q->where('primary_skill', '!=', '')
                ->orWhere('secondary_skill', '!=', '')
                ->orWhere('skills', '!=', '');
        })->count();
        $breakdown['skills_availability'] = $this->stats->percentage($withSkills, $total);

        $volunteers = (clone $query)->where(function ($q) {
            $q->where('is_disaster_volunteer', 1)
                ->orWhere('is_village_volunteer', 1);
        })->count();
        $breakdown['volunteer_participation'] = $this->stats->percentage($volunteers, $total);

        $withCommunityActivities = (clone $query)->where(function ($q) {
            $q->where('community_activities', '!=', '')
                ->orWhereNotNull('community_activities');
        })->count();
        $breakdown['community_organizations'] = $this->stats->percentage($withCommunityActivities, $total);

        $overallScore = count($breakdown) > 0
            ? round(array_sum($breakdown) / count($breakdown), 2)
            : 0;

        $strengths = [];
        $weaknesses = [];

        foreach ($breakdown as $component => $value) {
            $label = str_replace('_', ' ', ucfirst($component));
            if ($value >= 65) {
                $strengths[] = "{$label} is strong at " . number_format($value, 1) . "%";
            } elseif ($value < 40) {
                $weaknesses[] = "{$label} needs attention at " . number_format($value, 1) . "%";
            }
        }

        return [
            'overall_score' => $overallScore,
            'rating' => $this->getRating($overallScore),
            'breakdown' => array_map(fn($v) => round($v, 2), $breakdown),
            'strengths' => $strengths,
            'weaknesses' => $weaknesses,
        ];
    }

    protected function emptyScore(): array
    {
        return [
            'overall_score' => 0,
            'rating' => 'Needs Attention',
            'breakdown' => array_fill_keys($this->components, 0),
            'strengths' => [],
            'weaknesses' => ['No active residents found for analysis.'],
        ];
    }

    protected function calculateHouseholdStability(?int $villageId = null): float
    {
        $householdQuery = Household::query();

        if ($villageId) {
            $householdQuery->where('village_id', $villageId);
        }

        $households = $householdQuery->withCount(['residents' => fn($q) => $q->where('status', 'active')])
            ->get()
            ->pluck('residents_count')
            ->filter()
            ->values()
            ->toArray();

        if (empty($households)) {
            return 0;
        }

        $avgSize = round(array_sum($households) / count($households), 2);

        if ($avgSize >= 3 && $avgSize <= 5) {
            return 100;
        }

        if ($avgSize < 3) {
            $deviation = 3 - $avgSize;
            return max(0, 100 - ($deviation * 25));
        }

        $deviation = $avgSize - 5;
        return max(0, 100 - ($deviation * 20));
    }

    protected function calculateIncomeDistribution(?int $villageId = null): float
    {
        $query = $this->baseQuery($villageId)
            ->where('monthly_income', '>', 0)
            ->pluck('monthly_income')
            ->map(fn($v) => (float) $v)
            ->sort()
            ->values()
            ->toArray();

        if (count($query) < 2) {
            return 0;
        }

        $n = count($query);
        $sum = array_sum($query);
        $mean = $sum / $n;

        if ($mean <= 0) {
            return 0;
        }

        $sumAbsDiff = 0;
        foreach ($query as $value) {
            $sumAbsDiff += abs($value - $mean);
        }

        $gini = $sumAbsDiff / (2 * $n * $mean);

        $score = max(0, min(100, round((1 - $gini) * 100, 2)));

        return $score;
    }

    protected function getRating(float $score): string
    {
        return match (true) {
            $score >= 80 => 'Good',
            $score >= 60 => 'Average',
            $score >= 40 => 'Fair',
            default => 'Needs Attention',
        };
    }

    public function getAllVillageScores(): array
    {
        $villages = Village::where('status', 'active')->get();
        $result = [];

        foreach ($villages as $village) {
            $score = $this->getScore($village->id);

            $result[$village->village_name] = [
                'village_id' => $village->id,
                'village_code' => $village->village_code,
                'score' => $score['overall_score'],
                'rating' => $score['rating'],
            ];
        }

        return $result;
    }

    public function getVillageRankings(): array
    {
        $villages = Village::where('status', 'active')->get();
        $rankings = [];

        foreach ($villages as $village) {
            $score = $this->getScore($village->id);

            $rankings[] = [
                'village_id' => $village->id,
                'village_name' => $village->village_name,
                'village_code' => $village->village_code,
                'score' => $score['overall_score'],
                'rating' => $score['rating'],
            ];
        }

        usort($rankings, fn($a, $b) => $b['score'] <=> $a['score']);

        foreach ($rankings as $index => &$rank) {
            $rank['rank'] = $index + 1;
        }
        unset($rank);

        return $rankings;
    }

    public function getInsights(?int $villageId = null): array
    {
        $insights = [];
        $scoreData = $this->getScore($villageId);
        $overallScore = $scoreData['overall_score'];
        $rating = $scoreData['rating'];

        $insights[] = [
            'type' => match ($rating) {
                'Good' => 'success',
                'Average' => 'info',
                'Fair' => 'warning',
                'Needs Attention' => 'danger',
                default => 'info',
            },
            'icon' => 'bi-heart-pulse',
            'message' => "Socio-economic health score is {$overallScore}/100 ({$rating}).",
        ];

        if (!empty($scoreData['strengths'])) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'bi-check-circle',
                'message' => "Strengths: " . $scoreData['strengths'][0] . ".",
            ];
        }

        if (!empty($scoreData['weaknesses'])) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'bi-exclamation-triangle',
                'message' => "Weaknesses: " . $scoreData['weaknesses'][0] . ".",
            ];
        }

        $breakdown = $scoreData['breakdown'];
        $lowestKey = null;
        $lowestValue = 101;
        foreach ($breakdown as $key => $value) {
            if ($value < $lowestValue) {
                $lowestValue = $value;
                $lowestKey = $key;
            }
        }

        if ($lowestKey && $lowestValue < 30) {
            $label = str_replace('_', ' ', ucfirst($lowestKey));
            $insights[] = [
                'type' => 'danger',
                'icon' => 'bi-arrow-down-circle',
                'message' => "{$label} scores critically low at " . number_format($lowestValue, 1) . "%. Immediate action recommended.",
            ];
        }

        $highestKey = null;
        $highestValue = -1;
        foreach ($breakdown as $key => $value) {
            if ($value > $highestValue) {
                $highestValue = $value;
                $highestKey = $key;
            }
        }

        if ($highestKey && $highestValue >= 70) {
            $label = str_replace('_', ' ', ucfirst($highestKey));
            $insights[] = [
                'type' => 'success',
                'icon' => 'bi-arrow-up-circle',
                'message' => "{$label} is the strongest area at " . number_format($highestValue, 1) . "%.",
            ];
        }

        if (!$villageId) {
            $rankings = $this->getVillageRankings();
            if (count($rankings) > 1) {
                $highest = $rankings[0];
                $lowest = end($rankings);
                $insights[] = [
                    'type' => 'primary',
                    'icon' => 'bi-building',
                    'message' => "{$highest['village_name']} has the best socio-economic health ({$highest['score']}), while {$lowest['village_name']} needs the most support ({$lowest['score']}).",
                ];
            }
        }

        return $insights;
    }
}
