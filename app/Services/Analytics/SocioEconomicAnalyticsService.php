<?php

namespace App\Services\Analytics;

use App\Models\Resident;
use Illuminate\Database\Eloquent\Builder;

class SocioEconomicAnalyticsService
{
    public function __construct(
        protected StatisticsService $stats,
        protected OccupationAnalyticsService $occupation,
        protected SkillsAnalyticsService $skills,
        protected LivelihoodAnalyticsService $livelihood,
        protected CertificationAnalyticsService $certification,
        protected CommunityParticipationAnalyticsService $community,
        protected PopulationPyramidService $pyramid,
    ) {}

    protected function baseQuery(?int $villageId = null): Builder
    {
        $query = Resident::query()->where('status', 'active');
        if ($villageId) $query->where('village_id', $villageId);
        return $query;
    }

    public function getExecutiveSummary(?int $villageId = null): array
    {
        $occStats = $this->occupation->getSummaryStats($villageId);
        $skillStats = $this->skills->getSummaryStats($villageId);
        $liveStats = $this->livelihood->getSummaryStats($villageId);
        $certStats = $this->certification->getSummaryStats($villageId);
        $commStats = $this->community->getSummaryStats($villageId);
        $pyramidStats = $this->pyramid->getSummaryStats($villageId);

        return [
            'total_residents' => $pyramidStats['total'],
            'occupation_coverage' => $occStats['coverage_rate'],
            'skill_coverage' => $skillStats['skill_coverage'],
            'livelihood_coverage' => $liveStats['coverage_rate'],
            'certification_rate' => $certStats['qualification_rate'],
            'participation_rate' => $commStats['participation_rate'],
            'average_age' => $pyramidStats['average_age'],
            'median_age' => $pyramidStats['median_age'],
        ];
    }

    public function getIncomeAnalysis(?int $villageId = null): array
    {
        $incomeStats = $this->livelihood->getIncomeStats($villageId);
        $incomeRange = $this->livelihood->getIncomeRangeDistribution($villageId);
        $incomeByGender = $this->livelihood->getIncomeByGender($villageId);

        return [
            'stats' => $incomeStats,
            'range_distribution' => $incomeRange,
            'by_gender' => $incomeByGender,
        ];
    }

    public function getGovernmentAssistanceStats(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId);
        $total = $query->count();

        $memberships = [
            'pagibig' => (clone $query)->where('is_pagibig_member', 1)->count(),
            'philhealth' => (clone $query)->where('is_philhealth_member', 1)->count(),
            'sss' => (clone $query)->where('is_sss_member', 1)->count(),
            'gsis' => (clone $query)->where('is_gsis_member', 1)->count(),
        ];

        $assistance = [
            '4ps' => (clone $query)->where('is_4ps_member', 1)->count(),
            'senior_pension' => (clone $query)->where('is_senior_pensioner', 1)->count(),
        ];

        $totalWithMembership = (clone $query)->where(function ($q) {
            $q->where('is_pagibig_member', 1)
              ->orWhere('is_philhealth_member', 1)
              ->orWhere('is_sss_member', 1)
              ->orWhere('is_gsis_member', 1);
        })->count();

        $totalWithAssistance = (clone $query)->where(function ($q) {
            $q->where('is_4ps_member', 1)
              ->orWhere('is_senior_pensioner', 1);
        })->count();

        return [
            'total' => $total,
            'memberships' => $memberships,
            'assistance' => $assistance,
            'total_with_membership' => $totalWithMembership,
            'total_with_assistance' => $totalWithAssistance,
            'membership_rate' => $this->stats->percentage($totalWithMembership, $total),
            'assistance_rate' => $this->stats->percentage($totalWithAssistance, $total),
        ];
    }

    public function getSpecialPopulationStats(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId);
        $total = $query->count();

        return [
            'total' => $total,
            'pwd' => (clone $query)->where('is_pwd', 1)->count(),
            'solo_parent' => (clone $query)->where('is_solo_parent', 1)->count(),
            'senior_citizen' => (clone $query)->where('is_senior_citizen', 1)->count(),
            'youth' => (clone $query)->where('is_youth', 1)->count(),
            'student' => (clone $query)->where('is_student', 1)->count(),
            'ofw' => (clone $query)->where('is_ofw', 1)->count(),
            'infant' => (clone $query)->where('is_infant', 1)->count(),
        ];
    }

    public function getVillageComparison(): array
    {
        $villages = \App\Models\Village::where('status', 'active')->get();
        $result = [];
        foreach ($villages as $v) {
            $summary = $this->getExecutiveSummary($v->id);
            $income = $this->livelihood->getIncomeStats($v->id);
            $result[$v->village_name] = [
                'village_id' => $v->id,
                'village_code' => $v->village_code,
                'total' => $summary['total_residents'],
                'occupation_coverage' => $summary['occupation_coverage'],
                'skill_coverage' => $summary['skill_coverage'],
                'avg_income' => $income['average'],
                'median_income' => $income['median'],
            ];
        }
        return $result;
    }

    public function getSocioEconomicInsights(?int $villageId = null): array
    {
        $summary = $this->getExecutiveSummary($villageId);
        $govt = $this->getGovernmentAssistanceStats($villageId);
        $special = $this->getSpecialPopulationStats($villageId);
        $insights = [];

        if ($summary['total_residents'] > 0) {
            if ($summary['occupation_coverage'] > 0) {
                $insights[] = ['type' => 'info', 'message' => "{$summary['occupation_coverage']}% of residents have recorded occupations."];
            }
            if ($summary['skill_coverage'] > 0) {
                $insights[] = ['type' => 'info', 'message' => "{$summary['skill_coverage']}% of residents have recorded skills."];
            }
            if ($govt['membership_rate'] > 0) {
                $insights[] = ['type' => 'success', 'message' => "{$govt['membership_rate']}% of residents are members of government programs."];
            }
            if ($special['pwd'] > 0) {
                $insights[] = ['type' => 'warning', 'message' => "{$special['pwd']} residents are registered as PWDs."];
            }
            if ($special['ofw'] > 0) {
                $insights[] = ['type' => 'info', 'message' => "{$special['ofw']} residents are registered as OFWs."];
            }
            if ($govt['assistance_rate'] > 0) {
                $insights[] = ['type' => 'info', 'message' => "{$govt['assistance_rate']}% of residents receive government assistance."];
            }
        }

        return $insights;
    }
}
