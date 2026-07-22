<?php

namespace App\Services\Analytics;

use App\Models\Resident;
use App\Models\Village;
use Illuminate\Database\Eloquent\Builder;

class HealthcareAnalyticsService
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

    public function getSummaryStats(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId);
        $total = $query->count();

        $philhealthMembers = (clone $query)->where('is_philhealth_member', 1)->count();
        $withoutPhilhealth = $total - $philhealthMembers;

        $seniors = (clone $query)->where('is_senior_citizen', 1)->count();
        $seniorsWithCoverage = (clone $query)->where('is_senior_citizen', 1)
            ->where(function ($q) {
                $q->where('is_senior_id_holder', 1)
                    ->orWhere('is_senior_pensioner', 1)
                    ->orWhere('is_philhealth_member', 1);
            })->count();

        $pwd = (clone $query)->where('is_pwd', 1)->count();
        $pwdWithCoverage = (clone $query)->where('is_pwd', 1)
            ->where(function ($q) {
                $q->where('is_pwd_id_holder', 1)
                    ->orWhere('is_philhealth_member', 1);
            })->count();

        return [
            'total' => $total,
            'philhealth_members' => $philhealthMembers,
            'without_philhealth' => $withoutPhilhealth,
            'philhealth_rate' => $this->stats->percentage($philhealthMembers, $total),
            'senior_with_coverage' => $seniorsWithCoverage,
            'senior_coverage_rate' => $this->stats->percentage($seniorsWithCoverage, $seniors),
            'pwd_with_coverage' => $pwdWithCoverage,
            'pwd_coverage_rate' => $this->stats->percentage($pwdWithCoverage, $pwd),
        ];
    }

    public function getCoverageDistribution(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId);
        $total = $query->count();

        $philhealth = (clone $query)->where('is_philhealth_member', 1)->count();

        $seniorHealthcare = (clone $query)->where('is_senior_citizen', 1)
            ->where(function ($q) {
                $q->where('is_senior_id_holder', 1)
                    ->orWhere('is_senior_pensioner', 1)
                    ->orWhere('is_philhealth_member', 1);
            })->count();

        $pwdHealthcare = (clone $query)->where('is_pwd', 1)
            ->where(function ($q) {
                $q->where('is_pwd_id_holder', 1)
                    ->orWhere('is_philhealth_member', 1);
            })->count();

        return [
            'PhilHealth' => [
                'count' => (int) $philhealth,
                'percentage' => $this->stats->percentage($philhealth, $total),
            ],
            'Senior Healthcare' => [
                'count' => (int) $seniorHealthcare,
                'percentage' => $this->stats->percentage($seniorHealthcare, $total),
            ],
            'PWD Healthcare' => [
                'count' => (int) $pwdHealthcare,
                'percentage' => $this->stats->percentage($pwdHealthcare, $total),
            ],
        ];
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
                'philhealth_rate' => $stats['philhealth_rate'],
                'senior_rate' => $stats['senior_coverage_rate'],
                'pwd_rate' => $stats['pwd_coverage_rate'],
            ];
        }

        return $result;
    }

    public function getInsights(?int $villageId = null): array
    {
        $stats = $this->getSummaryStats($villageId);
        $insights = [];

        if ($stats['total'] > 0) {
            if ($stats['philhealth_rate'] >= 90) {
                $insights[] = [
                    'type' => 'success',
                    'message' => "PhilHealth coverage is excellent at {$stats['philhealth_rate']}% ({$stats['philhealth_members']} members).",
                ];
            } elseif ($stats['philhealth_rate'] < 50) {
                $insights[] = [
                    'type' => 'warning',
                    'message' => "Only {$stats['philhealth_rate']}% of residents have PhilHealth — {$stats['without_philhealth']} are uninsured.",
                ];
            }

            if ($stats['senior_coverage_rate'] < 100) {
                $insights[] = [
                    'type' => 'warning',
                    'message' => "Senior healthcare coverage is {$stats['senior_coverage_rate']}% — some seniors lack IDs or pension enrollment.",
                ];
            } elseif ($stats['senior_coverage_rate'] === 100.0) {
                $insights[] = [
                    'type' => 'success',
                    'message' => "All senior citizens have healthcare coverage (ID, pension, or PhilHealth).",
                ];
            }

            if ($stats['pwd_coverage_rate'] < 100) {
                $insights[] = [
                    'type' => 'warning',
                    'message' => "PWD healthcare coverage is {$stats['pwd_coverage_rate']}% — some PWDs lack IDs or PhilHealth.",
                ];
            } elseif ($stats['pwd_coverage_rate'] === 100.0) {
                $insights[] = [
                    'type' => 'success',
                    'message' => "All PWDs have healthcare coverage (PWD ID or PhilHealth).",
                ];
            }
        }

        return $insights;
    }
}
