<?php

namespace App\Services\Analytics;

use App\Models\Resident;
use App\Models\Village;
use Illuminate\Database\Eloquent\Builder;

class CommunityStructureService
{
    public function __construct(
        protected StatisticsService $stats
    ) {}

    protected function baseQuery(?int $villageId = null): Builder
    {
        $query = Resident::where('status', 'active');
        if ($villageId) {
            $query->where('village_id', $villageId);
        }
        return $query;
    }

    public function getSummaryStats(?int $villageId = null): array
    {
        $total = $this->baseQuery($villageId)->count();

        $working = $this->baseQuery($villageId)->whereBetween('age', [15, 64])->count();
        $dependent = $this->baseQuery($villageId)->where(function ($q) {
            $q->where('age', '<', 15)->orWhere('age', '>=', 65);
        })->count();
        $youth = $this->baseQuery($villageId)->whereBetween('age', [15, 30])->count();
        $senior = $this->baseQuery($villageId)->where('age', '>=', 60)->count();
        $pwd = $this->baseQuery($villageId)->where('is_pwd', true)->count();
        $soloParent = $this->baseQuery($villageId)->where('is_solo_parent', true)->count();
        $volunteer = $this->baseQuery($villageId)
            ->where(function ($q) {
                $q->where('is_disaster_volunteer', true)
                    ->orWhere('is_village_volunteer', true);
            })
            ->count();
        $children = $this->baseQuery($villageId)->whereBetween('age', [0, 14])->count();
        $male = $this->baseQuery($villageId)->where('gender', 'Male')->count();
        $female = $this->baseQuery($villageId)->where('gender', 'Female')->count();
        $student = $this->baseQuery($villageId)->where('is_student', true)->count();

        return [
            'total_population' => $total,
            'working_population' => $working,
            'dependent_population' => $dependent,
            'youth_population' => $youth,
            'senior_population' => $senior,
            'pwd_population' => $pwd,
            'solo_parent_population' => $soloParent,
            'volunteer_population' => $volunteer,
            'children_population' => $children,
            'male_population' => $male,
            'female_population' => $female,
            'student_population' => $student,
            'working_pct' => $this->stats->percentage($working, $total),
            'dependent_pct' => $this->stats->percentage($dependent, $total),
            'youth_pct' => $this->stats->percentage($youth, $total),
            'senior_pct' => $this->stats->percentage($senior, $total),
            'pwd_pct' => $this->stats->percentage($pwd, $total),
        ];
    }

    public function getStructureBreakdown(?int $villageId = null): array
    {
        $summary = $this->getSummaryStats($villageId);

        return [
            'Population Groups' => [
                'Working Age' => ['count' => $summary['working_population'], 'pct' => $summary['working_pct']],
                'Dependent' => ['count' => $summary['dependent_population'], 'pct' => $summary['dependent_pct']],
                'Youth (15-30)' => ['count' => $summary['youth_population'], 'pct' => $summary['youth_pct']],
                'Senior (60+)' => ['count' => $summary['senior_population'], 'pct' => $summary['senior_pct']],
                'Children (0-14)' => ['count' => $summary['children_population'], 'pct' => $this->stats->percentage($summary['children_population'], $summary['total_population'])],
            ],
            'Gender' => [
                'Male' => ['count' => $summary['male_population'], 'pct' => $this->stats->percentage($summary['male_population'], $summary['total_population'])],
                'Female' => ['count' => $summary['female_population'], 'pct' => $this->stats->percentage($summary['female_population'], $summary['total_population'])],
            ],
            'Special Groups' => [
                'PWD' => ['count' => $summary['pwd_population'], 'pct' => $summary['pwd_pct']],
                'Solo Parent' => ['count' => $summary['solo_parent_population'], 'pct' => $this->stats->percentage($summary['solo_parent_population'], $summary['total_population'])],
                'Volunteer' => ['count' => $summary['volunteer_population'], 'pct' => $this->stats->percentage($summary['volunteer_population'], $summary['total_population'])],
                'Student' => ['count' => $summary['student_population'], 'pct' => $this->stats->percentage($summary['student_population'], $summary['total_population'])],
            ],
        ];
    }

    public function getVillageComparison(): array
    {
        $villages = Village::where('status', 'active')->get();
        $results = [];

        foreach ($villages as $village) {
            $summary = $this->getSummaryStats($village->id);
            $results[] = [
                'village_id' => $village->id,
                'village_name' => $village->village_name,
                'working_population' => $summary['working_population'],
                'dependent_population' => $summary['dependent_population'],
                'youth_population' => $summary['youth_population'],
                'senior_population' => $summary['senior_population'],
                'pwd_population' => $summary['pwd_population'],
                'solo_parent_population' => $summary['solo_parent_population'],
                'volunteer_population' => $summary['volunteer_population'],
                'total_population' => $summary['total_population'],
            ];
        }

        return $results;
    }

    public function getRadarData(?int $villageId = null): array
    {
        $summary = $this->getSummaryStats($villageId);
        $total = max($summary['total_population'], 1);

        return [
            'labels' => ['Working Age', 'Youth', 'Senior', 'PWD', 'Solo Parent', 'Volunteer', 'Student'],
            'values' => [
                round(($summary['working_population'] / $total) * 100, 1),
                round(($summary['youth_population'] / $total) * 100, 1),
                round(($summary['senior_population'] / $total) * 100, 1),
                round(($summary['pwd_population'] / $total) * 100, 1),
                round(($summary['solo_parent_population'] / $total) * 100, 1),
                round(($summary['volunteer_population'] / $total) * 100, 1),
                round(($summary['student_population'] / $total) * 100, 1),
            ],
        ];
    }

    public function getInsights(?int $villageId = null): array
    {
        $insights = [];
        $summary = $this->getSummaryStats($villageId);

        $workingPct = $summary['working_pct'];
        if ($workingPct > 65) {
            $insights[] = [
                'title' => 'Strong Working-Age Population',
                'description' => "The working-age population accounts for {$workingPct}% of all residents, indicating economic potential.",
                'priority' => 'success',
                'icon' => 'briefcase',
            ];
        } elseif ($workingPct < 50) {
            $insights[] = [
                'title' => 'Low Working-Age Ratio',
                'description' => "Only {$workingPct}% are of working age, suggesting economic dependency challenges.",
                'priority' => 'warning',
                'icon' => 'exclamation-triangle',
            ];
        }

        if ($summary['pwd_population'] > 0) {
            $insights[] = [
                'title' => 'PWD Population',
                'description' => "{$summary['pwd_population']} residents ({$summary['pwd_pct']}%) are registered as PWD, requiring inclusive community programs.",
                'priority' => 'info',
                'icon' => 'universal-access',
            ];
        }

        if ($summary['senior_population'] > 0) {
            $seniorPct = $summary['senior_pct'];
            $insights[] = [
                'title' => 'Senior Citizen Population',
                'description' => "{$summary['senior_population']} senior citizens ({$seniorPct}%) need healthcare and social support services.",
                'priority' => 'info',
                'icon' => 'person',
            ];
        }

        return $insights;
    }
}
