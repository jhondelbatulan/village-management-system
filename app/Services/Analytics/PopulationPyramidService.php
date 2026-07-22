<?php

namespace App\Services\Analytics;

use App\Models\Resident;
use Illuminate\Database\Eloquent\Builder;

class PopulationPyramidService
{
    private const AGE_GROUPS = [
        '0–4' => [0, 4],
        '5–9' => [5, 9],
        '10–14' => [10, 14],
        '15–19' => [15, 19],
        '20–24' => [20, 24],
        '25–29' => [25, 29],
        '30–34' => [30, 34],
        '35–39' => [35, 39],
        '40–44' => [40, 44],
        '45–49' => [45, 49],
        '50–54' => [50, 54],
        '55–59' => [55, 59],
        '60–64' => [60, 64],
        '65–69' => [65, 69],
        '70–74' => [70, 74],
        '75+' => [75, 200],
    ];

    public function __construct(protected StatisticsService $stats) {}

    protected function baseQuery(?int $villageId = null): Builder
    {
        $query = Resident::query()->where('status', 'active')->whereNotNull('age');
        if ($villageId) $query->where('village_id', $villageId);
        return $query;
    }

    public function getPyramidData(?int $villageId = null): array
    {
        $male = $this->baseQuery($villageId)->where('gender', 'Male')
            ->selectRaw('age, COUNT(*) as count')->groupBy('age')
            ->pluck('count', 'age')->toArray();
        $female = $this->baseQuery($villageId)->where('gender', 'Female')
            ->selectRaw('age, COUNT(*) as count')->groupBy('age')
            ->pluck('count', 'age')->toArray();

        $labels = [];
        $maleData = [];
        $femaleData = [];
        $totalMale = 0;
        $totalFemale = 0;

        foreach (self::AGE_GROUPS as $label => [$min, $max]) {
            $labels[] = $label;
            $mCount = 0;
            $fCount = 0;
            for ($age = $min; $age <= $max; $age++) {
                $mCount += $male[$age] ?? 0;
                $fCount += $female[$age] ?? 0;
            }
            $maleData[] = -$mCount;
            $femaleData[] = $fCount;
            $totalMale += $mCount;
            $totalFemale += $fCount;
        }

        return [
            'labels' => $labels,
            'male' => $maleData,
            'female' => $femaleData,
            'total_male' => $totalMale,
            'total_female' => $totalFemale,
            'total' => $totalMale + $totalFemale,
        ];
    }

    public function getSummaryStats(?int $villageId = null): array
    {
        $pyramid = $this->getPyramidData($villageId);
        $query = $this->baseQuery($villageId);
        $ages = $query->pluck('age')->toArray();

        return [
            'total' => $pyramid['total'],
            'total_male' => $pyramid['total_male'],
            'total_female' => $pyramid['total_female'],
            'average_age' => $this->stats->mean($ages),
            'median_age' => $this->stats->median($ages),
            'youngest' => count($ages) ? min($ages) : 0,
            'oldest' => count($ages) ? max($ages) : 0,
        ];
    }

    public function getAgeGroupDistribution(?int $villageId = null): array
    {
        $pyramid = $this->getPyramidData($villageId);
        $result = [];
        $total = max($pyramid['total'], 1);
        for ($i = 0; $i < count($pyramid['labels']); $i++) {
            $male = abs($pyramid['male'][$i]);
            $female = $pyramid['female'][$i];
            $groupTotal = $male + $female;
            $result[$pyramid['labels'][$i]] = [
                'male' => $male,
                'female' => $female,
                'total' => $groupTotal,
                'percentage' => $this->stats->percentage($groupTotal, $total),
            ];
        }
        return $result;
    }

    public function getVillageComparison(): array
    {
        $villages = \App\Models\Village::where('status', 'active')->get();
        $result = [];
        foreach ($villages as $v) {
            $stats = $this->getSummaryStats($v->id);
            $result[$v->village_name] = [
                'village_id' => $v->id, 'village_code' => $v->village_code,
                'total' => $stats['total'], 'avg_age' => $stats['average_age'],
                'male' => $stats['total_male'], 'female' => $stats['total_female'],
            ];
        }
        return $result;
    }

    public function getInsights(?int $villageId = null): array
    {
        $stats = $this->getSummaryStats($villageId);
        $ageDist = $this->getAgeGroupDistribution($villageId);
        $insights = [];
        if ($stats['total'] > 0) {
            $insights[] = ['type' => 'info', 'message' => "Average age is {$stats['average_age']} years (Median: {$stats['median_age']})."];
            $ratio = $stats['total_female'] > 0 ? round($stats['total_male'] / $stats['total_female'], 2) : 0;
            $insights[] = ['type' => 'info', 'message' => "Male-to-Female ratio: {$ratio}:1."];
            $largest = collect($ageDist)->sortByDesc('total')->first();
            if ($largest) {
                $insights[] = ['type' => 'info', 'message' => "Largest age group: {$largest['total']} residents in the age range."];
            }
        }
        return $insights;
    }
}
