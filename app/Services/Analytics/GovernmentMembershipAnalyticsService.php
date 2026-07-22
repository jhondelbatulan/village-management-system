<?php

namespace App\Services\Analytics;

use App\Models\Resident;
use App\Models\Village;
use Illuminate\Database\Eloquent\Builder;

class GovernmentMembershipAnalyticsService
{
    protected array $memberships = [
        'PhilHealth' => 'is_philhealth_member',
        'SSS' => 'is_sss_member',
        'GSIS' => 'is_gsis_member',
        'Pag-IBIG' => 'is_pagibig_member',
        'National ID' => 'is_national_id_holder',
        'Senior Citizen ID' => 'is_senior_id_holder',
        'PWD ID' => 'is_pwd_id_holder',
        'Solo Parent ID' => 'is_solo_parent_id_holder',
        'Registered Voter' => 'voter_status',
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

    public function getSummaryStats(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId);
        $total = $query->count();

        $withMembership = (clone $query)->where(function ($q) {
            $q->where('is_philhealth_member', 1)
                ->orWhere('is_sss_member', 1)
                ->orWhere('is_gsis_member', 1)
                ->orWhere('is_pagibig_member', 1)
                ->orWhere('is_national_id_holder', 1)
                ->orWhere('is_senior_id_holder', 1)
                ->orWhere('is_pwd_id_holder', 1)
                ->orWhere('is_solo_parent_id_holder', 1)
                ->orWhere('voter_status', 1);
        })->count();

        return [
            'total' => $total,
            'with_any_membership' => $withMembership,
            'coverage_rate' => $this->stats->percentage($withMembership, $total),
        ];
    }

    public function getMembershipDistribution(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId);
        $total = $query->count();

        $counts = [];
        foreach ($this->memberships as $label => $field) {
            $counts[$label] = (clone $query)->where($field, 1)->count();
        }

        $result = [];
        foreach ($counts as $label => $count) {
            $result[$label] = [
                'count' => (int) $count,
                'percentage' => $this->stats->percentage($count, $total),
            ];
        }

        return $result;
    }

    public function getMembershipCounts(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId);

        $counts = [];
        foreach ($this->memberships as $label => $field) {
            $counts[$label] = (clone $query)->where($field, 1)->count();
        }

        return $counts;
    }

    public function getVillageComparison(): array
    {
        $villages = Village::where('status', 'active')->get();
        $result = [];

        foreach ($villages as $village) {
            $stats = $this->getSummaryStats($village->id);

            $result[$village->village_name] = [
                'village_id' => $village->id,
                'village_code' => $village->village_code,
                'total' => $stats['total'],
                'covered' => $stats['with_any_membership'],
                'coverage_rate' => $stats['coverage_rate'],
            ];
        }

        return $result;
    }

    public function getInsights(?int $villageId = null): array
    {
        $stats = $this->getSummaryStats($villageId);
        $distribution = $this->getMembershipDistribution($villageId);
        $insights = [];

        if ($stats['total'] > 0) {
            if ($stats['coverage_rate'] >= 80) {
                $insights[] = [
                    'type' => 'success',
                    'message' => "{$stats['coverage_rate']}% of residents have at least one government membership — excellent coverage.",
                ];
            } elseif ($stats['coverage_rate'] < 30) {
                $insights[] = [
                    'type' => 'warning',
                    'message' => "Only {$stats['coverage_rate']}% of residents have government memberships — consider registration drives.",
                ];
            }

            $maxMembership = '';
            $maxCount = 0;
            foreach ($distribution as $label => $data) {
                if ($data['count'] > $maxCount) {
                    $maxCount = $data['count'];
                    $maxMembership = $label;
                }
            }

            if ($maxMembership && $maxCount > 0) {
                $insights[] = [
                    'type' => 'info',
                    'message' => "Most common membership is {$maxMembership} with {$maxCount} holders ({$distribution[$maxMembership]['percentage']}%).",
                ];
            }

            $zeroMemberships = array_filter($distribution, fn($d) => $d['count'] === 0);
            if (!empty($zeroMemberships)) {
                $names = implode(', ', array_keys($zeroMemberships));
                $insights[] = [
                    'type' => 'warning',
                    'message' => "No members found for: {$names}.",
                ];
            }
        }

        return $insights;
    }
}
