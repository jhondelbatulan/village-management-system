<?php

namespace App\Services\Analytics;

use App\Models\Resident;
use App\Models\Village;
use Illuminate\Database\Eloquent\Builder;

class GovernmentAssistanceAnalyticsService
{
    protected array $programs = [
        '4Ps' => 'is_4ps_member',
        'Senior Pension' => 'is_senior_pensioner',
        'TUPAD' => 'is_tupad_beneficiary',
        'AKAP' => 'is_akap_beneficiary',
        'Educational Assistance' => 'is_educational_assistance',
        'Emergency Assistance' => 'is_emergency_assistance',
        'SLP' => 'is_slp_beneficiary',
        'Scholarship' => 'is_scholarship',
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

        $conditions = array_values($this->programs);
        $withAssistance = (clone $query)->where(function ($q) use ($conditions) {
            foreach ($conditions as $field) {
                $q->orWhere($field, 1);
            }
        })->orWhere('other_gov_assistance', '!=', '')->count();

        return [
            'total' => $total,
            'with_assistance' => $withAssistance,
            'coverage_rate' => $this->stats->percentage($withAssistance, $total),
        ];
    }

    public function getProgramDistribution(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId);
        $total = $query->count();

        $counts = [];
        foreach ($this->programs as $label => $field) {
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

    public function getProgramCounts(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId);

        $counts = [];
        foreach ($this->programs as $label => $field) {
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
            $programCounts = $this->getProgramCounts($village->id);
            $topProgram = array_search(max($programCounts), $programCounts) ?: 'None';

            $result[$village->village_name] = [
                'village_id' => $village->id,
                'village_code' => $village->village_code,
                'total' => $stats['total'],
                'with_assistance' => $stats['with_assistance'],
                'rate' => $stats['coverage_rate'],
                'top_program' => $topProgram,
            ];
        }

        return $result;
    }

    public function getBeneficiaryDistribution(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId);
        $residents = $query->get();

        $distribution = ['0' => 0, '1' => 0, '2' => 0, '3+' => 0];

        foreach ($residents as $resident) {
            $count = 0;
            foreach ($this->programs as $field) {
                if ($resident->{$field}) {
                    $count++;
                }
            }

            if ($count >= 3) {
                $distribution['3+']++;
            } else {
                $distribution[(string) $count]++;
            }
        }

        $total = array_sum($distribution);

        $result = [];
        foreach ($distribution as $label => $count) {
            $result[$label] = [
                'count' => (int) $count,
                'percentage' => $this->stats->percentage($count, $total),
            ];
        }

        return $result;
    }

    public function getMonthlyTrend(?int $villageId = null, int $months = 12): array
    {
        $result = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthKey = $date->format('Y-m');

            $query = $this->baseQuery($villageId)->where(function ($q) {
                foreach (array_values($this->programs) as $field) {
                    $q->orWhere($field, 1);
                }
            });

            $count = (clone $query)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $result[$monthKey] = $count;
        }

        return $result;
    }

    public function getInsights(?int $villageId = null): array
    {
        $stats = $this->getSummaryStats($villageId);
        $distribution = $this->getProgramDistribution($villageId);
        $insights = [];

        if ($stats['total'] > 0) {
            if ($stats['coverage_rate'] >= 50) {
                $insights[] = [
                    'type' => 'success',
                    'message' => "{$stats['with_assistance']} residents ({$stats['coverage_rate']}%) receive government assistance — strong coverage.",
                ];
            } elseif ($stats['coverage_rate'] < 10) {
                $insights[] = [
                    'type' => 'warning',
                    'message' => "Only {$stats['coverage_rate']}% of residents receive government assistance — consider outreach programs.",
                ];
            }

            $maxProgram = '';
            $maxCount = 0;
            foreach ($distribution as $label => $data) {
                if ($data['count'] > $maxCount) {
                    $maxCount = $data['count'];
                    $maxProgram = $label;
                }
            }

            if ($maxProgram && $maxCount > 0) {
                $insights[] = [
                    'type' => 'info',
                    'message' => "Most common program is {$maxProgram} with {$maxCount} beneficiaries ({$distribution[$maxProgram]['percentage']}%).",
                ];
            }

            $zeroPrograms = array_filter($distribution, fn($d) => $d['count'] === 0);
            if (!empty($zeroPrograms)) {
                $names = implode(', ', array_keys($zeroPrograms));
                $insights[] = [
                    'type' => 'warning',
                    'message' => "No beneficiaries found for: {$names}.",
                ];
            }
        }

        return $insights;
    }
}
