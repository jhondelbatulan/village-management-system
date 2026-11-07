<?php

namespace App\Services\Analytics;

use App\Models\Village;

class ExecutiveIntelligenceService
{
    public function __construct(
        protected GovernmentAssistanceAnalyticsService $govAssistance,
        protected GovernmentMembershipAnalyticsService $govMembership,
        protected HealthcareAnalyticsService $healthcare,
        protected VoterRegistrationAnalyticsService $voter,
        protected SocialWelfareAnalyticsService $socialWelfare,
        protected CommunityDevelopmentIndicatorsService $communityDev,
        protected VillageDevelopmentScoreService $devScore,
        protected SocioEconomicHealthScoreService $socioScore,
        protected CommunityReadinessService $readiness,
        protected PopulationAnalyticsService $population,
        protected EmploymentAnalyticsService $employment,
        protected SkillsAnalyticsService $skills,
        protected OccupationAnalyticsService $occupation,
        protected EducationAnalyticsService $education,
        protected HouseholdAnalyticsService $household,
        protected StatisticsService $stats
    ) {}

    public function getExecutiveInsights(?int $villageId = null): array
    {
        $insights = [];

        if ($villageId) {
            $insights = array_merge($insights, $this->getVillageInsights($villageId));
        } else {
            $insights = array_merge($insights, $this->getSystemInsights());
        }

        usort($insights, function ($a, $b) {
            $priorityOrder = ['high' => 0, 'medium' => 1, 'low' => 2];
            return $priorityOrder[$a['priority']] <=> $priorityOrder[$b['priority']];
        });

        return $insights;
    }

    protected function getSystemInsights(): array
    {
        $insights = [];
        $villages = Village::where('status', 'active')->get();

        $populationComparison = $this->population->getVillageComparison();
        if (!empty($populationComparison)) {
            $highest = collect($populationComparison)->sortByDesc('total')->first();
            $lowest = collect($populationComparison)->sortBy('total')->first();

            if ($highest && $lowest && $highest['village']->id !== $lowest['village']->id) {
                $insights[] = [
                    'type' => 'population',
                    'category' => 'general',
                    'priority' => 'medium',
                    'message' => "{$highest['village']->village_name} has the highest population ({$highest['total']}) while {$lowest['village']->village_name} has the lowest ({$lowest['total']}).",
                    'recommendation' => 'Consider resource distribution based on population density.',
                ];
            }
        }

        $employmentComparison = $this->employment->getVillageComparison();
        if (!empty($employmentComparison)) {
            $rates = array_column($employmentComparison, 'employment_rate');
            $villageNames = array_keys($employmentComparison);

            if (count($rates) > 1) {
                $maxRate = max($rates);
                $minRate = min($rates);
                $maxIdx = array_search($maxRate, $rates);
                $minIdx = array_search($minRate, $rates);

                if ($maxRate !== $minRate) {
                    $insights[] = [
                        'type' => 'employment',
                        'category' => 'employment',
                        'priority' => $minRate < 50 ? 'high' : 'medium',
                        'message' => "Employment rate ranges from {$minRate}% ({$villageNames[$minIdx]}) to {$maxRate}% ({$villageNames[$maxIdx]}).",
                        'recommendation' => 'Launch livelihood programs in villages with lower employment rates.',
                    ];
                }
            }
        }

        $skillsStats = $this->skills->getSummaryStats();
        if ($skillsStats['total'] > 0 && $skillsStats['skill_coverage'] < 50) {
            $insights[] = [
                'type' => 'skills',
                'category' => 'skills',
                'priority' => 'high',
                'message' => "Only {$skillsStats['skill_coverage']}% of residents have recorded skills.",
                'recommendation' => 'Implement skills training and inventory programs across all villages.',
            ];
        }

        $educationStats = $this->education->getSummaryStats();
        if ($educationStats['total_residents'] > 0) {
            if ($educationStats['education_rate'] < 70) {
                $insights[] = [
                    'type' => 'education',
                    'category' => 'education',
                    'priority' => 'high',
                    'message' => "Education rate is only {$educationStats['education_rate']}%.",
                    'recommendation' => 'Coordinate with DepEd and local colleges for adult education and literacy programs.',
                ];
            }

            if ($educationStats['scholarship_rate'] > 0) {
                $insights[] = [
                    'type' => 'education',
                    'category' => 'education',
                    'priority' => 'low',
                    'message' => "{$educationStats['scholars']} residents ({$educationStats['scholarship_rate']}%) are on scholarship.",
                    'recommendation' => 'Expand scholarship partnerships to increase coverage.',
                ];
            }
        }

        $readiness = $this->readiness->getScore();
        if ($readiness['overall_score'] < 60) {
            $insights[] = [
                'type' => 'readiness',
                'category' => 'development',
                'priority' => 'high',
                'message' => "Community readiness score is {$readiness['overall_score']}/100 ({$readiness['rating']}).",
                'recommendation' => 'Strengthen disaster preparedness and volunteer recruitment programs.',
            ];
        }

        $occupationStats = $this->occupation->getSummaryStats();
        if ($occupationStats['total'] > 0 && $occupationStats['coverage_rate'] < 60) {
            $insights[] = [
                'type' => 'occupation',
                'category' => 'employment',
                'priority' => 'medium',
                'message' => "Occupation data coverage is only {$occupationStats['coverage_rate']}% — top occupation: {$occupationStats['top_occupation']}.",
                'recommendation' => 'Conduct comprehensive livelihood surveys to improve data completeness.',
            ];
        }

        $govAssistStats = $this->govAssistance->getSummaryStats();
        if (!empty($govAssistStats['total_recipients'])) {
            $insights[] = [
                'type' => 'government',
                'category' => 'government',
                'priority' => 'medium',
                'message' => "{$govAssistStats['total_recipients']} residents are receiving government assistance ({$govAssistStats['assistance_rate']}%).",
                'recommendation' => 'Monitor program effectiveness and ensure equitable distribution across villages.',
            ];
        }

        $govMemberStats = $this->govMembership->getSummaryStats();
        if (!empty($govMemberStats['total_with_membership'])) {
            $insights[] = [
                'type' => 'government',
                'category' => 'government',
                'priority' => 'low',
                'message' => "{$govMemberStats['total_with_membership']} residents ({$govMemberStats['membership_rate']}%) have government memberships/IDs.",
                'recommendation' => 'Continue registration drives for PhilHealth, SSS, and Pag-IBIG.',
            ];
        }

        return $insights;
    }

    protected function getVillageInsights(int $villageId): array
    {
        $insights = [];
        $village = Village::find($villageId);

        if (!$village) {
            return [];
        }

        $populationStats = $this->population->getVillageStats($villageId);
        $employmentStats = $this->employment->getSummaryStats($villageId);
        $skillsStats = $this->skills->getSummaryStats($villageId);
        $educationStats = $this->education->getSummaryStats($villageId);
        $occupationStats = $this->occupation->getSummaryStats($villageId);
        $readiness = $this->readiness->getScore($villageId);

        $insights[] = [
            'type' => 'population',
            'category' => 'general',
            'priority' => 'medium',
            'message' => "{$village->village_name} has {$populationStats['total']} active residents.",
            'recommendation' => 'Review population growth trends for infrastructure planning.',
        ];

        if ($employmentStats['total_residents'] > 0) {
            if ($employmentStats['employment_rate'] < 60) {
                $insights[] = [
                    'type' => 'employment',
                    'category' => 'employment',
                    'priority' => 'high',
                    'message' => "Employment rate in {$village->village_name} is {$employmentStats['employment_rate']}%.",
                    'recommendation' => 'Prioritize job placement and livelihood programs for this village.',
                ];
            }

            if ($employmentStats['average_income'] > 0) {
                $insights[] = [
                    'type' => 'income',
                    'category' => 'employment',
                    'priority' => 'medium',
                    'message' => "Average monthly income in {$village->village_name}: ₱" . number_format($employmentStats['average_income'], 2) . ".",
                    'recommendation' => $employmentStats['average_income'] < 10000
                        ? 'Income levels are below average — consider economic empowerment programs.'
                        : 'Income levels are stable — maintain current economic programs.',
                ];
            }

            if ($employmentStats['ofw_rate'] > 10) {
                $insights[] = [
                    'type' => 'ofw',
                    'category' => 'employment',
                    'priority' => 'low',
                    'message' => "{$employmentStats['ofw']} OFWs ({$employmentStats['ofw_rate']}%) from {$village->village_name}.",
                    'recommendation' => 'Maintain OFW support services and family welfare programs.',
                ];
            }
        }

        if ($skillsStats['total'] > 0) {
            if ($skillsStats['skill_coverage'] < 40) {
                $insights[] = [
                    'type' => 'skills',
                    'category' => 'skills',
                    'priority' => 'high',
                    'message' => "Only {$skillsStats['skill_coverage']}% of {$village->village_name} residents have recorded skills.",
                    'recommendation' => 'Organize skills mapping and training workshops.',
                ];
            }
        }

        if ($educationStats['total_residents'] > 0) {
            if ($educationStats['education_rate'] < 60) {
                $insights[] = [
                    'type' => 'education',
                    'category' => 'education',
                    'priority' => 'high',
                    'message' => "Education rate in {$village->village_name} is {$educationStats['education_rate']}%.",
                    'recommendation' => 'Partner with educational institutions for community learning centers.',
                ];
            }
        }

        if ($readiness['overall_score'] < 50) {
            $insights[] = [
                'type' => 'readiness',
                'category' => 'development',
                'priority' => 'high',
                'message' => "{$village->village_name} readiness score is {$readiness['overall_score']}/100 ({$readiness['rating']}).",
                'recommendation' => 'Emergency: strengthen disaster preparedness and community organizing.',
            ];
        }

        return $insights;
    }

    public function getTopInsights(?int $villageId = null, int $limit = 10): array
    {
        $allInsights = $this->getExecutiveInsights($villageId);
        return array_slice($allInsights, 0, $limit);
    }

    public function getVillageHighlights(?int $villageId = null): array
    {
        $villages = Village::where('status', 'active')
            ->when($villageId, fn ($q) => $q->where('id', $villageId))
            ->get();
        $highlights = [];

        foreach ($villages as $village) {
            $popStats = $this->population->getVillageStats($village->id);
            $empStats = $this->employment->getSummaryStats($village->id);
            $skillStats = $this->skills->getSummaryStats($village->id);
            $eduStats = $this->education->getSummaryStats($village->id);
            $readiness = $this->readiness->getScore($village->id);

            $highlights[$village->id] = [
                'village' => [
                    'id' => $village->id,
                    'name' => $village->village_name,
                    'code' => $village->village_code,
                ],
                'population' => $popStats['total'],
                'employment_rate' => $empStats['employment_rate'],
                'average_income' => $empStats['average_income'],
                'skill_coverage' => $skillStats['skill_coverage'],
                'education_rate' => $eduStats['education_rate'],
                'readiness_score' => $readiness['overall_score'],
                'readiness_rating' => $readiness['rating'],
            ];
        }

        return $highlights;
    }

    public function getComparativeAnalysis(?int $villageId = null): array
    {
        $villages = Village::where('status', 'active')
            ->when($villageId, fn ($q) => $q->where('id', $villageId))
            ->get();
        $metrics = [];

        foreach ($villages as $village) {
            $empStats = $this->employment->getSummaryStats($village->id);
            $skillStats = $this->skills->getSummaryStats($village->id);
            $eduStats = $this->education->getSummaryStats($village->id);
            $readiness = $this->readiness->getScore($village->id);

            $metrics[$village->id] = [
                'village_name' => $village->village_name,
                'employment_rate' => $empStats['employment_rate'],
                'unemployment_rate' => $empStats['unemployment_rate'],
                'average_income' => $empStats['average_income'],
                'skill_coverage' => $skillStats['skill_coverage'],
                'education_rate' => $eduStats['education_rate'],
                'readiness_score' => $readiness['overall_score'],
            ];
        }

        $rankings = [
            'by_employment' => collect($metrics)->sortByDesc('employment_rate')->values()->all(),
            'by_income' => collect($metrics)->sortByDesc('average_income')->values()->all(),
            'by_skills' => collect($metrics)->sortByDesc('skill_coverage')->values()->all(),
            'by_education' => collect($metrics)->sortByDesc('education_rate')->values()->all(),
            'by_readiness' => collect($metrics)->sortByDesc('readiness_score')->values()->all(),
        ];

        $averages = [];
        if (!empty($metrics)) {
            $metricKeys = ['employment_rate', 'unemployment_rate', 'average_income', 'skill_coverage', 'education_rate', 'readiness_score'];
            foreach ($metricKeys as $key) {
                $values = array_column($metrics, $key);
                $averages[$key] = $this->stats->mean($values);
            }
        }

        return [
            'villages' => $metrics,
            'rankings' => $rankings,
            'averages' => $averages,
            'total_villages' => count($villages),
        ];
    }
}
