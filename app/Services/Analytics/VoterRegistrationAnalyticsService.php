<?php

namespace App\Services\Analytics;

use App\Models\Resident;
use App\Models\Village;
use Illuminate\Database\Eloquent\Builder;

class VoterRegistrationAnalyticsService
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

        $votingAgePopulation = (clone $query)->where('age', '>=', 18)->count();
        $registeredVoters = (clone $query)->where('age', '>=', 18)->where('voter_status', 1)->count();
        $nonRegisteredVoters = $votingAgePopulation - $registeredVoters;

        return [
            'total' => $total,
            'voting_age_population' => $votingAgePopulation,
            'registered_voters' => $registeredVoters,
            'non_registered_voters' => $nonRegisteredVoters,
            'registration_rate' => $this->stats->percentage($registeredVoters, $votingAgePopulation),
        ];
    }

    public function getRegistrationByGender(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId)->where('age', '>=', 18);

        $genders = ['Male', 'Female'];
        $result = [];

        foreach ($genders as $gender) {
            $total = (clone $query)->where('gender', $gender)->count();
            $registered = (clone $query)->where('gender', $gender)->where('voter_status', 1)->count();

            $result[$gender] = [
                'registered' => (int) $registered,
                'total' => (int) $total,
                'rate' => $this->stats->percentage($registered, $total),
            ];
        }

        return $result;
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
                'voting_age' => $stats['voting_age_population'],
                'registered' => $stats['registered_voters'],
                'rate' => $stats['registration_rate'],
            ];
        }

        return $result;
    }

    public function getInsights(?int $villageId = null): array
    {
        $stats = $this->getSummaryStats($villageId);
        $genderStats = $this->getRegistrationByGender($villageId);
        $insights = [];

        if ($stats['total'] > 0) {
            if ($stats['registration_rate'] >= 80) {
                $insights[] = [
                    'type' => 'success',
                    'message' => "{$stats['registered_voters']} of {$stats['voting_age_population']} voting-age residents are registered ({$stats['registration_rate']}%).",
                ];
            } elseif ($stats['registration_rate'] < 40) {
                $insights[] = [
                    'type' => 'warning',
                    'message' => "Voter registration rate is only {$stats['registration_rate']}% — {$stats['non_registered_voters']} eligible residents are unregistered.",
                ];
            } else {
                $insights[] = [
                    'type' => 'info',
                    'message' => "{$stats['registered_voters']} of {$stats['voting_age_population']} voting-age residents are registered ({$stats['registration_rate']}%).",
                ];
            }

            foreach ($genderStats as $gender => $data) {
                if ($data['total'] > 0 && $data['rate'] < 40) {
                    $insights[] = [
                        'type' => 'warning',
                        'message' => "Only {$data['rate']}% of {$gender} voting-age residents are registered ({$data['registered']}/{$data['total']}).",
                    ];
                }
            }

            $maleRate = $genderStats['Male']['rate'] ?? 0;
            $femaleRate = $genderStats['Female']['rate'] ?? 0;
            if ($maleRate > 0 && $femaleRate > 0) {
                $diff = abs($maleRate - $femaleRate);
                if ($diff > 10) {
                    $higher = $maleRate > $femaleRate ? 'Male' : 'Female';
                    $insights[] = [
                        'type' => 'info',
                        'message' => "Gender registration gap: {$higher} registration rate leads by {$diff} percentage points.",
                    ];
                }
            }
        }

        return $insights;
    }
}
