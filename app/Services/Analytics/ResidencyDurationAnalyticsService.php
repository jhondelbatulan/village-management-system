<?php

namespace App\Services\Analytics;

use App\Models\Resident;
use App\Models\Village;
use Illuminate\Database\Eloquent\Builder;

class ResidencyDurationAnalyticsService
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
        $years = $this->baseQuery($villageId)
            ->whereNotNull('years_of_residency')
            ->pluck('years_of_residency')
            ->values()
            ->toArray();

        $avg = !empty($years) ? round(array_sum($years) / count($years), 2) : 0;

        $brackets = $this->getDurationBrackets($villageId);

        return [
            'average_years' => $avg,
            'total_residents' => $this->baseQuery($villageId)->count(),
            'with_data' => count($years),
            'brackets' => $brackets,
        ];
    }

    public function getDurationBrackets(?int $villageId = null): array
    {
        $years = $this->baseQuery($villageId)
            ->whereNotNull('years_of_residency')
            ->pluck('years_of_residency')
            ->values()
            ->toArray();

        $brackets = [
            'Less than 1 Year' => 0,
            '1-5 Years' => 0,
            '6-10 Years' => 0,
            '11-20 Years' => 0,
            'More than 20 Years' => 0,
        ];

        foreach ($years as $y) {
            if ($y < 1) $brackets['Less than 1 Year']++;
            elseif ($y <= 5) $brackets['1-5 Years']++;
            elseif ($y <= 10) $brackets['6-10 Years']++;
            elseif ($y <= 20) $brackets['11-20 Years']++;
            else $brackets['More than 20 Years']++;
        }

        return $this->stats->frequencyCount($brackets);
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
                'average_years' => $summary['average_years'],
                'total_residents' => $summary['total_residents'],
            ];
        }

        return collect($results)->sortByDesc('average_years')->values()->toArray();
    }

    public function getInsights(?int $villageId = null): array
    {
        $insights = [];
        $summary = $this->getSummaryStats($villageId);

        if ($summary['average_years'] > 10) {
            $insights[] = [
                'title' => 'High Residency Stability',
                'description' => "Average residency of {$summary['average_years']} years indicates strong community stability.",
                'priority' => 'success',
                'icon' => 'house-heart',
            ];
        } elseif ($summary['average_years'] < 3) {
            $insights[] = [
                'title' => 'Low Residency Duration',
                'description' => "Average residency of {$summary['average_years']} years suggests high population turnover.",
                'priority' => 'warning',
                'icon' => 'arrow-left-right',
            ];
        }

        return $insights;
    }
}
