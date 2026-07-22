<?php

namespace App\Services\Analytics;

use App\Models\Household;
use App\Models\Resident;
use App\Models\Village;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CommunityDevelopmentIndicatorsService
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

    public function getIndicators(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId);

        $total = (clone $query)->count();

        if ($total === 0) {
            return $this->emptyIndicators();
        }

        $withEmploymentStatus = (clone $query)->whereNotNull('employment_status')
            ->where('employment_status', '!=', '')->count();
        $employed = (clone $query)->where('employment_status', 'Employed')->count();
        $employmentRate = $this->stats->percentage($employed, $withEmploymentStatus);

        $withEducation = (clone $query)->whereNotNull('educational_attainment')
            ->where('educational_attainment', '!=', '')->count();
        $educationIndex = $this->stats->percentage($withEducation, $total);

        $withSkills = (clone $query)->where(function ($q) {
            $q->where('primary_skill', '!=', '')
                ->orWhere('secondary_skill', '!=', '')
                ->orWhere('skills', '!=', '');
        })->count();
        $skillsIndex = $this->stats->percentage($withSkills, $total);

        $philhealthMembers = (clone $query)->where('is_philhealth_member', 1)->count();
        $healthcareCoverage = $this->stats->percentage($philhealthMembers, $total);

        $withAssistance = (clone $query)->where(function ($q) {
            $q->where('is_4ps_member', 1)
                ->orWhere('is_senior_pensioner', 1)
                ->orWhere('is_tupad_beneficiary', 1)
                ->orWhere('is_akap_beneficiary', 1)
                ->orWhere('is_educational_assistance', 1)
                ->orWhere('is_emergency_assistance', 1)
                ->orWhere('is_slp_beneficiary', 1)
                ->orWhere('is_scholarship', 1);
        })->count();
        $assistanceCoverage = $this->stats->percentage($withAssistance, $total);

        $votingAge = (clone $query)->where('age', '>=', 18)->count();
        $registeredVoters = (clone $query)->where('age', '>=', 18)->where('voter_status', 1)->count();
        $voterRegistrationRate = $this->stats->percentage($registeredVoters, $votingAge);

        $ageUnder15 = (clone $query)->where('age', '<', 15)->count();
        $age15to64 = (clone $query)->whereBetween('age', [15, 64])->count();
        $ageOver64 = (clone $query)->where('age', '>', 64)->count();
        $dependencyRatio = $age15to64 > 0
            ? round((($ageUnder15 + $ageOver64) / $age15to64) * 100, 2)
            : 0;

        $withCommunityActivities = (clone $query)->where(function ($q) {
            $q->where('community_activities', '!=', '')
                ->orWhereNotNull('community_activities');
        })->count();
        $communityParticipationRate = $this->stats->percentage($withCommunityActivities, $total);

        $volunteers = (clone $query)->where(function ($q) {
            $q->where('is_disaster_volunteer', 1)
                ->orWhere('is_village_volunteer', 1);
        })->count();
        $volunteerRate = $this->stats->percentage($volunteers, $total);

        return [
            'employment_rate' => [
                'value' => $employmentRate,
                'label' => 'Employment Rate',
                'formatted' => number_format($employmentRate, 1) . '%',
            ],
            'unemployment_rate' => [
                'value' => round(100 - $employmentRate, 2),
                'label' => 'Unemployment Rate',
                'formatted' => number_format(100 - $employmentRate, 1) . '%',
            ],
            'education_index' => [
                'value' => $educationIndex,
                'label' => 'Education Index',
                'formatted' => number_format($educationIndex, 1) . '%',
            ],
            'skills_index' => [
                'value' => $skillsIndex,
                'label' => 'Skills Index',
                'formatted' => number_format($skillsIndex, 1) . '%',
            ],
            'healthcare_coverage' => [
                'value' => $healthcareCoverage,
                'label' => 'Healthcare Coverage',
                'formatted' => number_format($healthcareCoverage, 1) . '%',
            ],
            'assistance_coverage' => [
                'value' => $assistanceCoverage,
                'label' => 'Assistance Coverage',
                'formatted' => number_format($assistanceCoverage, 1) . '%',
            ],
            'voter_registration_rate' => [
                'value' => $voterRegistrationRate,
                'label' => 'Voter Registration Rate',
                'formatted' => number_format($voterRegistrationRate, 1) . '%',
            ],
            'dependency_ratio' => [
                'value' => $dependencyRatio,
                'label' => 'Dependency Ratio',
                'formatted' => number_format($dependencyRatio, 1) . '%',
            ],
            'community_participation_rate' => [
                'value' => $communityParticipationRate,
                'label' => 'Community Participation Rate',
                'formatted' => number_format($communityParticipationRate, 1) . '%',
            ],
            'volunteer_rate' => [
                'value' => $volunteerRate,
                'label' => 'Volunteer Rate',
                'formatted' => number_format($volunteerRate, 1) . '%',
            ],
        ];
    }

    protected function emptyIndicators(): array
    {
        $labels = [
            'employment_rate' => 'Employment Rate',
            'unemployment_rate' => 'Unemployment Rate',
            'education_index' => 'Education Index',
            'skills_index' => 'Skills Index',
            'healthcare_coverage' => 'Healthcare Coverage',
            'assistance_coverage' => 'Assistance Coverage',
            'voter_registration_rate' => 'Voter Registration Rate',
            'dependency_ratio' => 'Dependency Ratio',
            'community_participation_rate' => 'Community Participation Rate',
            'volunteer_rate' => 'Volunteer Rate',
        ];

        $result = [];
        foreach ($labels as $key => $label) {
            $result[$key] = [
                'value' => 0,
                'label' => $label,
                'formatted' => '0.0%',
            ];
        }

        return $result;
    }

    public function getVillageRankings(): array
    {
        $villages = Village::where('status', 'active')->get();
        $rankings = [];

        foreach ($villages as $village) {
            $indicators = $this->getIndicators($village->id);
            $values = array_column($indicators, 'value');
            $compositeScore = count($values) > 0 ? round(array_sum($values) / count($values), 2) : 0;

            $rankings[] = [
                'village_id' => $village->id,
                'village_name' => $village->village_name,
                'village_code' => $village->village_code,
                'composite_score' => $compositeScore,
                'indicators' => $indicators,
            ];
        }

        usort($rankings, fn($a, $b) => $b['composite_score'] <=> $a['composite_score']);

        foreach ($rankings as $index => &$rank) {
            $rank['rank'] = $index + 1;
        }
        unset($rank);

        return $rankings;
    }

    public function getIndicatorTrend(?int $villageId = null, string $indicator = 'employment_rate', int $months = 12): array
    {
        $result = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $monthEnd = now()->subMonths($i)->endOfMonth();

            $query = Resident::where('status', 'active')
                ->where('created_at', '<=', $monthEnd);

            if ($villageId) {
                $query->where('village_id', $villageId);
            }

            $total = (clone $query)->count();

            if ($total === 0) {
                $result[$month] = ['indicator' => $indicator, 'value' => 0, 'formatted' => '0.0%'];
                continue;
            }

            $value = match ($indicator) {
                'employment_rate' => $this->calcRateAtMonth($query, $total, 'employment_status', 'Employed', 'employment_status'),
                'unemployment_rate' => 100 - $this->calcRateAtMonth($query, $total, 'employment_status', 'Employed', 'employment_status'),
                'education_index' => $this->calcNonNullRate($query, $total, 'educational_attainment'),
                'skills_index' => $this->calcSkillsRate($query, $total),
                'healthcare_coverage' => $this->calcBoolRate($query, $total, 'is_philhealth_member'),
                'voter_registration_rate' => $this->calcVoterRate($query),
                'community_participation_rate' => $this->calcNonNullRate($query, $total, 'community_activities'),
                'volunteer_rate' => $this->calcVolunteerRate($query, $total),
                default => 0,
            };

            $result[$month] = [
                'indicator' => $indicator,
                'value' => $value,
                'formatted' => number_format($value, 1) . '%',
            ];
        }

        return $result;
    }

    protected function calcRateAtMonth($query, int $total, string $field, string $value, string $filterField): float
    {
        $withField = (clone $query)->whereNotNull($filterField)
            ->where($filterField, '!=', '')->count();
        $matchCount = (clone $query)->where($filterField, $value)->count();

        return $this->stats->percentage($matchCount, $withField);
    }

    protected function calcNonNullRate($query, int $total, string $field): float
    {
        $count = (clone $query)->whereNotNull($field)
            ->where($field, '!=', '')->count();

        return $this->stats->percentage($count, $total);
    }

    protected function calcSkillsRate($query, int $total): float
    {
        $count = (clone $query)->where(function ($q) {
            $q->where('primary_skill', '!=', '')
                ->orWhere('secondary_skill', '!=', '')
                ->orWhere('skills', '!=', '');
        })->count();

        return $this->stats->percentage($count, $total);
    }

    protected function calcBoolRate($query, int $total, string $field): float
    {
        $count = (clone $query)->where($field, 1)->count();

        return $this->stats->percentage($count, $total);
    }

    protected function calcVoterRate($query): float
    {
        $votingAge = (clone $query)->where('age', '>=', 18)->count();
        $registered = (clone $query)->where('age', '>=', 18)->where('voter_status', 1)->count();

        return $this->stats->percentage($registered, $votingAge);
    }

    protected function calcVolunteerRate($query, int $total): float
    {
        $count = (clone $query)->where(function ($q) {
            $q->where('is_disaster_volunteer', 1)
                ->orWhere('is_village_volunteer', 1);
        })->count();

        return $this->stats->percentage($count, $total);
    }

    public function getInsights(?int $villageId = null): array
    {
        $insights = [];
        $indicators = $this->getIndicators($villageId);

        $employmentRate = $indicators['employment_rate']['value'];
        if ($employmentRate >= 70) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'bi-briefcase',
                'message' => "Strong employment rate at {$indicators['employment_rate']['formatted']}.",
            ];
        } elseif ($employmentRate < 50) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'bi-briefcase',
                'message' => "Low employment rate at {$indicators['employment_rate']['formatted']}. Livelihood programs recommended.",
            ];
        }

        $educationIndex = $indicators['education_index']['value'];
        if ($educationIndex >= 80) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'bi-book',
                'message' => "High educational attainment at {$indicators['education_index']['formatted']}.",
            ];
        } elseif ($educationIndex < 60) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'bi-book',
                'message' => "Education index at {$indicators['education_index']['formatted']}. Scholarship programs may help.",
            ];
        }

        $healthcare = $indicators['healthcare_coverage']['value'];
        if ($healthcare < 80) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'bi-heart-pulse',
                'message' => "Healthcare coverage at {$indicators['healthcare_coverage']['formatted']}. PhilHealth enrollment drives recommended.",
            ];
        }

        $voterRate = $indicators['voter_registration_rate']['value'];
        if ($voterRate < 70) {
            $insights[] = [
                'type' => 'info',
                'icon' => 'bi-check2-square',
                'message' => "Voter registration rate at {$indicators['voter_registration_rate']['formatted']}. Registration campaigns may help.",
            ];
        }

        $dependency = $indicators['dependency_ratio']['value'];
        if ($dependency > 60) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'bi-people',
                'message' => "High dependency ratio at {$indicators['dependency_ratio']['formatted']}. Support programs needed for dependents.",
            ];
        }

        $volunteerRate = $indicators['volunteer_rate']['value'];
        if ($volunteerRate >= 10) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'bi-hand-thumbs-up',
                'message' => "Strong volunteer participation at {$indicators['volunteer_rate']['formatted']}.",
            ];
        }

        return $insights;
    }
}
