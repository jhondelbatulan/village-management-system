<?php

namespace App\Services\Analytics;

use App\Models\Resident;
use App\Models\Village;
use Illuminate\Database\Eloquent\Builder;

class SocialWelfareAnalyticsService
{
    protected array $categories = [
        'is_senior_citizen' => 'Senior Citizens',
        'is_pwd' => 'PWD',
        'is_solo_parent' => 'Solo Parents',
        'is_indigenous' => 'Indigenous',
        'is_veteran' => 'Veterans',
        'is_youth' => 'Youth',
        'is_student' => 'Students',
        'is_ofw' => 'OFW',
        'is_infant' => 'Infants',
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

        $total = (clone $query)->count();

        return [
            'total' => $total,
            'seniors' => (clone $query)->where('is_senior_citizen', 1)->count(),
            'pwd' => (clone $query)->where('is_pwd', 1)->count(),
            'solo_parent' => (clone $query)->where('is_solo_parent', 1)->count(),
            'indigenous' => (clone $query)->where('is_indigenous', 1)->count(),
            'veteran' => (clone $query)->where('is_veteran', 1)->count(),
            'youth' => (clone $query)->where('is_youth', 1)->count(),
            'student' => (clone $query)->where('is_student', 1)->count(),
            'ofw' => (clone $query)->where('is_ofw', 1)->count(),
            'infant' => (clone $query)->where('is_infant', 1)->count(),
        ];
    }

    public function getCategoryDistribution(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId);
        $total = (clone $query)->count();

        $counts = [];
        foreach ($this->categories as $field => $label) {
            $counts[$label] = (clone $query)->where($field, 1)->count();
        }

        return $this->stats->frequencyCount($counts);
    }

    public function getVillageComparison(): array
    {
        $villages = Village::where('status', 'active')->get();
        $result = [];

        foreach ($villages as $village) {
            $query = $this->baseQuery($village->id);
            $total = (clone $query)->count();

            $result[$village->village_name] = [
                'village_id' => $village->id,
                'village_code' => $village->village_code,
                'total' => $total,
                'seniors' => (clone $query)->where('is_senior_citizen', 1)->count(),
                'pwd' => (clone $query)->where('is_pwd', 1)->count(),
                'solo_parent' => (clone $query)->where('is_solo_parent', 1)->count(),
                'indigenous' => (clone $query)->where('is_indigenous', 1)->count(),
                'veteran' => (clone $query)->where('is_veteran', 1)->count(),
            ];
        }

        return $result;
    }

    public function getTrend(?int $villageId = null, int $months = 12): array
    {
        $result = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = now()->subMonths($i)->endOfMonth();

            $query = Resident::where('status', 'active')
                ->where('created_at', '<=', $monthEnd);

            if ($villageId) {
                $query->where('village_id', $villageId);
            }

            $result[$month] = [
                'seniors' => (clone $query)->where('is_senior_citizen', 1)->count(),
                'pwd' => (clone $query)->where('is_pwd', 1)->count(),
                'solo_parent' => (clone $query)->where('is_solo_parent', 1)->count(),
            ];
        }

        return $result;
    }

    public function getInsights(?int $villageId = null): array
    {
        $insights = [];
        $stats = $this->getSummaryStats($villageId);
        $total = $stats['total'];

        if ($total === 0) {
            return [
                [
                    'type' => 'info',
                    'icon' => 'bi-info-circle',
                    'message' => 'No active residents found for analysis.',
                ],
            ];
        }

        if ($stats['seniors'] > 0) {
            $pct = $this->stats->percentage($stats['seniors'], $total);
            $insights[] = [
                'type' => 'warning',
                'icon' => 'bi-person-workspace',
                'message' => "Senior citizens comprise {$pct}% of the population ({$stats['seniors']}).",
            ];
        }

        if ($stats['pwd'] > 0) {
            $pct = $this->stats->percentage($stats['pwd'], $total);
            $insights[] = [
                'type' => 'info',
                'icon' => 'bi-universal-access',
                'message' => "PWD residents comprise {$pct}% of the population ({$stats['pwd']}).",
            ];
        }

        if ($stats['solo_parent'] > 0) {
            $pct = $this->stats->percentage($stats['solo_parent'], $total);
            $insights[] = [
                'type' => 'info',
                'icon' => 'bi-person',
                'message' => "Solo parents comprise {$pct}% of the population ({$stats['solo_parent']}).",
            ];
        }

        if ($stats['youth'] > 0) {
            $pct = $this->stats->percentage($stats['youth'], $total);
            $insights[] = [
                'type' => 'success',
                'icon' => 'bi-person-video',
                'message' => "Youth population is {$pct}% ({$stats['youth']}).",
            ];
        }

        if ($stats['student'] > 0) {
            $pct = $this->stats->percentage($stats['student'], $total);
            $insights[] = [
                'type' => 'success',
                'icon' => 'bi-book',
                'message' => "Students comprise {$pct}% of the population ({$stats['student']}).",
            ];
        }

        if ($stats['ofw'] > 0) {
            $insights[] = [
                'type' => 'primary',
                'icon' => 'bi-airplane',
                'message' => "{$stats['ofw']} resident(s) are registered as OFWs.",
            ];
        }

        if ($stats['indigenous'] > 0) {
            $insights[] = [
                'type' => 'info',
                'icon' => 'bi-people',
                'message' => "{$stats['indigenous']} indigenous community member(s) recorded.",
            ];
        }

        if ($stats['veteran'] > 0) {
            $insights[] = [
                'type' => 'primary',
                'icon' => 'bi-award',
                'message' => "{$stats['veteran']} veteran(s) recognized in the community.",
            ];
        }

        if (!$villageId) {
            $comparison = $this->getVillageComparison();
            if (!empty($comparison)) {
                $highest = collect($comparison)->sortByDesc('seniors')->first();
                if ($highest && $highest['seniors'] > 0) {
                    $vName = $this->getVillageName($highest['village_id']);
                    $insights[] = [
                        'type' => 'warning',
                        'icon' => 'bi-building',
                        'message' => "{$vName} has the most senior citizens ({$highest['seniors']}).",
                    ];
                }
            }
        }

        return $insights;
    }

    protected function getVillageName(int $villageId): string
    {
        return Village::find($villageId)?->village_name ?? 'Unknown';
    }
}
