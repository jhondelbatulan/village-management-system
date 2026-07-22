<?php

namespace App\Services\Analytics;

use App\Models\Resident;
use Illuminate\Database\Eloquent\Builder;

class ResidentProfileService
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

    public function getGovernmentMembershipStats(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId);
        $total = $query->count();

        $pagibig = (clone $query)->where('is_pagibig_member', true)->count();
        $philhealth = (clone $query)->where('is_philhealth_member', true)->count();
        $sss = (clone $query)->where('is_sss_member', true)->count();
        $gsis = (clone $query)->where('is_gsis_member', true)->count();
        $any = (clone $query)->where(function ($q) {
            $q->where('is_pagibig_member', true)
              ->orWhere('is_philhealth_member', true)
              ->orWhere('is_sss_member', true)
              ->orWhere('is_gsis_member', true);
        })->count();

        return [
            'total' => $total,
            'pagibig' => $pagibig,
            'philhealth' => $philhealth,
            'sss' => $sss,
            'gsis' => $gsis,
            'any_membership' => $any,
            'any_membership_rate' => $this->stats->percentage($any, $total),
            'pagibig_rate' => $this->stats->percentage($pagibig, $total),
            'philhealth_rate' => $this->stats->percentage($philhealth, $total),
            'sss_rate' => $this->stats->percentage($sss, $total),
            'gsis_rate' => $this->stats->percentage($gsis, $total),
        ];
    }

    public function getGovernmentAssistanceStats(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId);
        $total = $query->count();

        $fourPs = (clone $query)->where('is_4ps_member', true)->count();
        $seniorPension = (clone $query)->where('is_senior_pensioner', true)->count();
        $anyAssistance = (clone $query)->where(function ($q) {
            $q->where('is_4ps_member', true)
              ->orWhere('is_senior_pensioner', true)
              ->orWhereNotNull('other_gov_assistance');
        })->count();

        return [
            'total' => $total,
            'four_ps' => $fourPs,
            'four_ps_rate' => $this->stats->percentage($fourPs, $total),
            'senior_pension' => $seniorPension,
            'senior_pension_rate' => $this->stats->percentage($seniorPension, $total),
            'any_assistance' => $anyAssistance,
            'any_assistance_rate' => $this->stats->percentage($anyAssistance, $total),
        ];
    }

    public function getSpecialPopulationStats(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId);
        $total = $query->count();

        $pwd = (clone $query)->where('is_pwd', true)->count();
        $soloParent = (clone $query)->where('is_solo_parent', true)->count();
        $senior = (clone $query)->where('is_senior_citizen', true)->count();
        $youth = (clone $query)->where('is_youth', true)->count();
        $student = (clone $query)->where('is_student', true)->count();
        $ofw = (clone $query)->where('is_ofw', true)->count();
        $infant = (clone $query)->where('is_infant', true)->count();
        $anySpecial = (clone $query)->where(function ($q) {
            $q->where('is_pwd', true)
              ->orWhere('is_solo_parent', true)
              ->orWhere('is_senior_citizen', true)
              ->orWhere('is_youth', true)
              ->orWhere('is_student', true)
              ->orWhere('is_ofw', true)
              ->orWhere('is_infant', true);
        })->count();

        return [
            'total' => $total,
            'pwd' => $pwd,
            'pwd_rate' => $this->stats->percentage($pwd, $total),
            'solo_parent' => $soloParent,
            'solo_parent_rate' => $this->stats->percentage($soloParent, $total),
            'senior_citizen' => $senior,
            'senior_citizen_rate' => $this->stats->percentage($senior, $total),
            'youth' => $youth,
            'youth_rate' => $this->stats->percentage($youth, $total),
            'student' => $student,
            'student_rate' => $this->stats->percentage($student, $total),
            'ofw' => $ofw,
            'ofw_rate' => $this->stats->percentage($ofw, $total),
            'infant' => $infant,
            'infant_rate' => $this->stats->percentage($infant, $total),
            'any_special' => $anySpecial,
            'any_special_rate' => $this->stats->percentage($anySpecial, $total),
        ];
    }

    public function getBloodTypeDistribution(?int $villageId = null): array
    {
        $counts = $this->baseQuery($villageId)
            ->selectRaw('blood_type, COUNT(*) as count')
            ->whereNotNull('blood_type')
            ->where('blood_type', '!=', '')
            ->groupBy('blood_type')
            ->pluck('count', 'blood_type')
            ->toArray();

        return $this->stats->frequencyCount($counts);
    }

    public function getYearsOfResidencyDistribution(?int $villageId = null): array
    {
        $counts = $this->baseQuery($villageId)
            ->selectRaw("CASE
                WHEN years_of_residency IS NULL THEN 'Not Specified'
                WHEN years_of_residency = 0 THEN 'New (< 1 year)'
                WHEN years_of_residency BETWEEN 1 AND 5 THEN '1-5 years'
                WHEN years_of_residency BETWEEN 6 AND 10 THEN '6-10 years'
                WHEN years_of_residency BETWEEN 11 AND 20 THEN '11-20 years'
                ELSE '20+ years'
            END as range, COUNT(*) as count")
            ->groupBy('range')
            ->pluck('count', 'range')
            ->toArray();

        return $this->stats->frequencyCount($counts);
    }

    public function getVillageComparison(): array
    {
        $villages = \App\Models\Village::where('status', 'active')->get();
        $result = [];

        foreach ($villages as $village) {
            $special = $this->getSpecialPopulationStats($village->id);
            $govt = $this->getGovernmentMembershipStats($village->id);

            $result[$village->village_name] = [
                'village_id' => $village->id,
                'village_code' => $village->village_code,
                'total' => $special['total'],
                'pwd_rate' => $special['pwd_rate'],
                'senior_rate' => $special['senior_citizen_rate'],
                'youth_rate' => $special['youth_rate'],
                'student_rate' => $special['student_rate'],
                'govt_membership_rate' => $govt['any_membership_rate'],
            ];
        }

        return $result;
    }

    public function getInsights(?int $villageId = null): array
    {
        $special = $this->getSpecialPopulationStats($villageId);
        $govtMembership = $this->getGovernmentMembershipStats($villageId);
        $govtAssistance = $this->getGovernmentAssistanceStats($villageId);
        $insights = [];

        if ($special['total'] > 0) {
            if ($special['pwd_rate'] > 5) {
                $insights[] = ['type' => 'info', 'message' => "{$special['pwd']} PWDs ({$special['pwd_rate']}%) — ensure accessibility programs."];
            }
            if ($special['senior_citizen_rate'] > 10) {
                $insights[] = ['type' => 'info', 'message' => "{$special['senior_citizen']} senior citizens ({$special['senior_citizen_rate']}%) — consider senior programs."];
            }
            if ($special['youth_rate'] > 30) {
                $insights[] = ['type' => 'success', 'message' => "Strong youth population of {$special['youth_rate']}% — invest in youth development."];
            }
            if ($govtMembership['any_membership_rate'] < 50) {
                $insights[] = ['type' => 'warning', 'message' => "Only {$govtMembership['any_membership_rate']}% have government memberships — encourage enrollment."];
            }
            if ($govtAssistance['four_ps_rate'] > 0) {
                $insights[] = ['type' => 'info', 'message' => "{$govtAssistance['four_ps']} 4Ps beneficiaries ({$govtAssistance['four_ps_rate']}%)."];
            }
        }

        return $insights;
    }
}
