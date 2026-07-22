<?php

namespace App\Services\Analytics;

use App\Models\Resident;
use App\Models\Village;
use Illuminate\Database\Eloquent\Builder;

class VillageDevelopmentScoreService
{
    protected array $weights = [
        'employment_rate' => 15,
        'education_index' => 15,
        'healthcare_coverage' => 15,
        'income_score' => 10,
        'skills_index' => 10,
        'assistance_coverage' => 10,
        'voter_registration_rate' => 10,
        'community_participation_rate' => 10,
        'dependency_ratio' => 5,
    ];

    public function __construct(
        protected CommunityDevelopmentIndicatorsService $cdi,
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
        $indicators = $this->cdi->getIndicators($villageId);
        $incomeScore = $this->calculateIncomeScore($villageId);

        $breakdown = [];
        $totalWeightedScore = 0;
        $totalWeight = 0;

        $indicatorScores = [
            'employment_rate' => $indicators['employment_rate']['value'],
            'education_index' => $indicators['education_index']['value'],
            'healthcare_coverage' => $indicators['healthcare_coverage']['value'],
            'income_score' => $incomeScore,
            'skills_index' => $indicators['skills_index']['value'],
            'assistance_coverage' => $indicators['assistance_coverage']['value'],
            'voter_registration_rate' => $indicators['voter_registration_rate']['value'],
            'community_participation_rate' => $indicators['community_participation_rate']['value'],
            'dependency_ratio' => max(0, 100 - $indicators['dependency_ratio']['value']),
        ];

        foreach ($this->weights as $indicator => $weight) {
            $score = $indicatorScores[$indicator] ?? 0;
            $breakdown[$indicator] = [
                'score' => round($score, 2),
                'weight' => $weight,
                'weighted' => round($score * $weight / 100, 2),
            ];
            $totalWeightedScore += $score * $weight;
            $totalWeight += $weight;
        }

        $overallScore = $totalWeight > 0 ? round($totalWeightedScore / $totalWeight, 2) : 0;

        $factors = [];
        $concerns = [];

        foreach ($breakdown as $key => $data) {
            $label = str_replace('_', ' ', ucfirst($key));
            if ($data['score'] >= 65) {
                $factors[] = "{$label} is strong at " . number_format($data['score'], 1) . "%";
            } elseif ($data['score'] < 40) {
                $concerns[] = "{$label} needs improvement at " . number_format($data['score'], 1) . "%";
            }
        }

        return [
            'overall_score' => $overallScore,
            'rating' => $this->getRating($overallScore),
            'breakdown' => $breakdown,
            'factors' => $factors,
            'concerns' => $concerns,
        ];
    }

    protected function calculateIncomeScore(?int $villageId = null): float
    {
        $query = $this->baseQuery($villageId)
            ->where('monthly_income', '>', 0);

        $total = (clone $query)->count();

        if ($total === 0) {
            return 0;
        }

        $aboveThreshold = (clone $query)->where('monthly_income', '>=', 10000)->count();

        return $this->stats->percentage($aboveThreshold, $total);
    }

    protected function getRating(float $score): string
    {
        return match (true) {
            $score >= 80 => 'Excellent',
            $score >= 65 => 'Very Good',
            $score >= 50 => 'Good',
            $score >= 35 => 'Fair',
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
                'Excellent' => 'success',
                'Very Good' => 'success',
                'Good' => 'info',
                'Fair' => 'warning',
                'Needs Attention' => 'danger',
                default => 'info',
            },
            'icon' => 'bi-speedometer',
            'message' => "Overall village development score is {$overallScore}/100 ({$rating}).",
        ];

        if (!empty($scoreData['factors'])) {
            $topFactor = $scoreData['factors'][0];
            $insights[] = [
                'type' => 'success',
                'icon' => 'bi-check-circle',
                'message' => "Key strength: {$topFactor}.",
            ];
        }

        if (!empty($scoreData['concerns'])) {
            $topConcern = $scoreData['concerns'][0];
            $insights[] = [
                'type' => 'warning',
                'icon' => 'bi-exclamation-triangle',
                'message' => "Area for improvement: {$topConcern}.",
            ];
        }

        $breakdown = $scoreData['breakdown'];
        $lowestKey = null;
        $lowestScore = 101;
        foreach ($breakdown as $key => $data) {
            if ($data['score'] < $lowestScore) {
                $lowestScore = $data['score'];
                $lowestKey = $key;
            }
        }

        if ($lowestKey && $lowestScore < 40) {
            $label = str_replace('_', ' ', ucfirst($lowestKey));
            $insights[] = [
                'type' => 'danger',
                'icon' => 'bi-arrow-down-circle',
                'message' => "{$label} scores critically low at " . number_format($lowestScore, 1) . "%. Priority intervention recommended.",
            ];
        }

        if (!$villageId) {
            $rankings = $this->getVillageRankings();
            if (count($rankings) > 1) {
                $highest = $rankings[0];
                $lowest = end($rankings);
                $insights[] = [
                    'type' => 'primary',
                    'icon' => 'bi-trophy',
                    'message' => "{$highest['village_name']} leads with a score of {$highest['score']}, while {$lowest['village_name']} trails at {$lowest['score']}.",
                ];
            }
        }

        return $insights;
    }
}
