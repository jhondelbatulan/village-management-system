<?php

namespace App\Services\Analytics;

use App\Models\Resident;
use App\Models\Village;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AnalyticsSearchService
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

    public function search(?string $query, ?int $villageId = null): array
    {
        $search = trim((string) ($query ?? ''));

        if ($search === '') {
            return [
                'residents' => collect(),
                'total' => 0,
                'query' => $search,
            ];
        }

        $builder = $this->baseQuery($villageId);

        $builder->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('middle_name', 'like', "%{$search}%")
                ->orWhere('occupation', 'like', "%{$search}%")
                ->orWhere('primary_skill', 'like', "%{$search}%")
                ->orWhere('secondary_skill', 'like', "%{$search}%")
                ->orWhere('skills', 'like', "%{$search}%")
                ->orWhere('educational_attainment', 'like', "%{$search}%")
                ->orWhere('income_source', 'like', "%{$search}%")
                ->orWhere('other_gov_assistance', 'like', "%{$search}%")
                ->orWhere('community_activities', 'like', "%{$search}%")
                ->orWhere('special_qualifications', 'like', "%{$search}%");
        });

        $residents = $builder->orderBy('last_name')->orderBy('first_name')->get();
        $total = $residents->count();

        return [
            'residents' => $residents,
            'total' => $total,
            'query' => $search,
        ];
    }

    public function advancedFilter(array $filters, ?int $villageId = null): Builder
    {
        $builder = $this->baseQuery($villageId);

        if (!$villageId && !empty($filters['village'])) {
            $builder->where('village_id', $filters['village']);
        }

        if (!empty($filters['program'])) {
            $programMap = [
                '4ps' => 'is_4ps_member',
                'senior_pension' => 'is_senior_pensioner',
                'tupad' => 'is_tupad_beneficiary',
                'akap' => 'is_akap_beneficiary',
                'educational_assistance' => 'is_educational_assistance',
                'emergency_assistance' => 'is_emergency_assistance',
                'slp' => 'is_slp_beneficiary',
                'scholarship' => 'is_scholarship',
            ];

            $builder->where(function ($q) use ($filters, $programMap) {
                foreach ((array) $filters['program'] as $program) {
                    if (isset($programMap[$program])) {
                        $q->orWhere($programMap[$program], 1);
                    }
                }
            });
        }

        if (!empty($filters['membership'])) {
            $membershipMap = [
                'philhealth' => 'is_philhealth_member',
                'pagibig' => 'is_pagibig_member',
                'sss' => 'is_sss_member',
                'gsis' => 'is_gsis_member',
                'national_id' => 'is_national_id_holder',
                'senior_id' => 'is_senior_id_holder',
                'pwd_id' => 'is_pwd_id_holder',
                'solo_parent_id' => 'is_solo_parent_id_holder',
                'voter' => 'voter_status',
            ];

            $builder->where(function ($q) use ($filters, $membershipMap) {
                foreach ((array) $filters['membership'] as $member) {
                    if (isset($membershipMap[$member])) {
                        $q->orWhere($membershipMap[$member], 1);
                    }
                }
            });
        }

        if (!empty($filters['employment_status'])) {
            $statuses = (array) $filters['employment_status'];
            $builder->whereIn('employment_status', $statuses);
        }

        if (!empty($filters['education'])) {
            $builder->where('educational_attainment', $filters['education']);
        }

        if (!empty($filters['income_range'])) {
            $builder->where('monthly_income_range', $filters['income_range']);
        }

        if (!empty($filters['skills'])) {
            $skillSearch = $filters['skills'];
            $builder->where(function ($q) use ($skillSearch) {
                $q->where('primary_skill', 'like', "%{$skillSearch}%")
                    ->orWhere('secondary_skill', 'like', "%{$skillSearch}%")
                    ->orWhere('skills', 'like', "%{$skillSearch}%");
            });
        }

        if (!empty($filters['healthcare'])) {
            if ($filters['healthcare'] === 'philhealth') {
                $builder->where('is_philhealth_member', 1);
            }
        }

        if (!empty($filters['resident_type'])) {
            $typeMap = [
                'senior' => 'is_senior_citizen',
                'pwd' => 'is_pwd',
                'solo_parent' => 'is_solo_parent',
                'youth' => 'is_youth',
                'student' => 'is_student',
                'ofw' => 'is_ofw',
            ];

            $types = (array) $filters['resident_type'];
            $builder->where(function ($q) use ($types, $typeMap) {
                foreach ($types as $type) {
                    if (isset($typeMap[$type])) {
                        $q->orWhere($typeMap[$type], 1);
                    }
                }
            });
        }

        if (!empty($filters['date_from'])) {
            $builder->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('created_at', '<=', $filters['date_to'] . ' 23:59:59');
        }

        return $builder;
    }

    public function getFilterOptions(): array
    {
        $allResidents = Resident::query()->where('status', 'active')->get();

        $villages = Village::where('status', 'active')
            ->pluck('village_name', 'id')
            ->sort()
            ->values()
            ->toArray();

        $occupations = $allResidents->pluck('occupation')->filter()->unique()->sort()->values()->toArray();
        $skills = collect();
        foreach ($allResidents as $r) {
            if ($r->primary_skill) $skills->push($r->primary_skill);
            if ($r->secondary_skill) $skills->push($r->secondary_skill);
            if ($r->skills) {
                foreach (array_map('trim', explode(',', $r->skills)) as $s) {
                    if ($s !== '') $skills->push($s);
                }
            }
        }
        $skillsList = $skills->unique()->sort()->values()->toArray();

        $educationalAttainments = $allResidents->pluck('educational_attainment')->filter()->unique()->sort()->values()->toArray();
        $incomeSources = $allResidents->pluck('income_source')->filter()->unique()->sort()->values()->toArray();
        $incomeRanges = $allResidents->pluck('monthly_income_range')->filter()->unique()->values()->toArray();

        $incomeRangeOrder = ['Below ₱5,000', '₱5,000 - ₱10,000', '₱10,001 - ₱20,000', '₱20,001 - ₱40,000', '₱40,001 - ₱60,000', '₱60,001 - ₱100,000', 'Above ₱100,000'];
        $orderedIncomeRanges = [];
        foreach ($incomeRangeOrder as $range) {
            if (in_array($range, $incomeRanges)) {
                $orderedIncomeRanges[] = $range;
            }
        }

        $employmentStatuses = $allResidents->pluck('employment_status')->filter()->unique()->sort()->values()->toArray();

        $programs = [
            '4ps' => '4Ps',
            'senior_pension' => 'Senior Pension',
            'tupad' => 'TUPAD',
            'akap' => 'AKAP',
            'educational_assistance' => 'Educational Assistance',
            'emergency_assistance' => 'Emergency Assistance',
            'slp' => 'SLP',
            'scholarship' => 'Scholarship',
        ];

        $memberships = [
            'philhealth' => 'PhilHealth',
            'pagibig' => 'Pag-IBIG',
            'sss' => 'SSS',
            'gsis' => 'GSIS',
            'national_id' => 'National ID',
            'senior_id' => 'Senior Citizen ID',
            'pwd_id' => 'PWD ID',
            'solo_parent_id' => 'Solo Parent ID',
            'voter' => 'Registered Voter',
        ];

        $residentTypes = [
            'senior' => 'Senior Citizen',
            'pwd' => 'Person with Disability',
            'solo_parent' => 'Solo Parent',
            'youth' => 'Youth',
            'student' => 'Student',
            'ofw' => 'OFW',
        ];

        $healthcareOptions = [
            'philhealth' => 'PhilHealth Member',
        ];

        return [
            'villages' => $villages,
            'occupations' => $occupations,
            'skills' => $skillsList,
            'education_levels' => $educationalAttainments,
            'income_sources' => $incomeSources,
            'income_ranges' => $orderedIncomeRanges,
            'employment_statuses' => $employmentStatuses,
            'programs' => array_values($programs),
            'memberships' => array_values($memberships),
            'resident_types' => array_values($residentTypes),
            'healthcare_coverage' => array_values($healthcareOptions),
        ];
    }
}
