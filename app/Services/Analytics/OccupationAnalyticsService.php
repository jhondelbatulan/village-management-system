<?php

namespace App\Services\Analytics;

use App\Models\Resident;
use App\Models\Village;
use App\Support\SqlHelper;
use Illuminate\Database\Eloquent\Builder;

class OccupationAnalyticsService
{
    public function __construct(protected StatisticsService $stats) {}

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
        $withOccupation = (clone $query)->whereNotNull('occupation')->where('occupation', '!=', '')->count();
        $top = (clone $query)->selectRaw('occupation, COUNT(*) as count')
            ->whereNotNull('occupation')->where('occupation', '!=', '')
            ->groupBy('occupation')->orderByDesc('count')->first();

        return [
            'total' => $total,
            'with_occupation' => $withOccupation,
            'coverage_rate' => $this->stats->percentage($withOccupation, $total),
            'top_occupation' => $top?->occupation ?? '—',
            'top_count' => $top?->count ?? 0,
        ];
    }

    public function getOccupationDistribution(?int $villageId = null): array
    {
        $counts = $this->baseQuery($villageId)
            ->selectRaw('occupation, COUNT(*) as count')
            ->whereNotNull('occupation')->where('occupation', '!=', '')
            ->groupBy('occupation')->orderByDesc('count')
            ->pluck('count', 'occupation')->toArray();

        return $this->stats->frequencyCount($counts);
    }

    public function getTopOccupations(?int $villageId = null, int $limit = 10): array
    {
        return $this->baseQuery($villageId)
            ->selectRaw('occupation, COUNT(*) as count')
            ->whereNotNull('occupation')->where('occupation', '!=', '')
            ->groupBy('occupation')->orderByDesc('count')
            ->limit($limit)
            ->pluck('count', 'occupation')->toArray();
    }

    public function getLeastCommonOccupations(?int $villageId = null, int $limit = 10): array
    {
        return $this->baseQuery($villageId)
            ->selectRaw('occupation, COUNT(*) as count')
            ->whereNotNull('occupation')->where('occupation', '!=', '')
            ->groupBy('occupation')->orderBy('count')->orderBy('occupation')
            ->limit($limit)
            ->pluck('count', 'occupation')->toArray();
    }

    public function getOccupationByGender(?int $villageId = null): array
    {
        $rows = $this->baseQuery($villageId)
            ->selectRaw('occupation, gender, COUNT(*) as count')
            ->whereNotNull('occupation')->where('occupation', '!=', '')
            ->groupBy('occupation', 'gender')->get();

        $result = [];
        foreach ($rows as $row) {
            $result[$row->occupation][$row->gender] = $row->count;
        }

        return $result;
    }

    public function getVillageComparison(): array
    {
        $villages = Village::where('status', 'active')->get();
        $result = [];
        foreach ($villages as $v) {
            $stats = $this->getSummaryStats($v->id);
            $top = $this->getTopOccupations($v->id, 3);
            $result[$v->village_name] = [
                'village_id' => $v->id, 'village_code' => $v->village_code,
                'total' => $stats['total'], 'coverage_rate' => $stats['coverage_rate'],
                'top_occupation' => $stats['top_occupation'],
                'top_3' => $top,
            ];
        }

        return $result;
    }

    public function getOccupationRankings(): array
    {
        $dist = $this->getOccupationDistribution();
        $rank = 1;
        $result = [];
        foreach ($dist as $occ => $data) {
            $result[] = ['rank' => $rank++, 'occupation' => $occ, 'count' => $data['count'], 'percentage' => $data['percentage']];
        }

        return $result;
    }

    public function getOccupationTrend(?int $villageId = null, int $months = 12): array
    {
        $rows = $this->baseQuery($villageId)
            ->selectRaw(SqlHelper::dateFormat('created_at', '%Y-%m').' as month, occupation, COUNT(*) as count')
            ->whereNotNull('occupation')->where('occupation', '!=', '')
            ->where('created_at', '>=', now()->subMonths($months))
            ->groupBy('month', 'occupation')->orderBy('month')
            ->get()->groupBy('month');

        $result = [];
        foreach ($rows as $month => $entries) {
            foreach ($entries as $e) {
                $result[$month][$e->occupation] = $e->count;
            }
        }

        return $result;
    }

    public function getInsights(?int $villageId = null): array
    {
        $stats = $this->getSummaryStats($villageId);
        $top = $this->getTopOccupations($villageId, 5);
        $insights = [];
        if ($stats['total'] > 0) {
            if ($stats['coverage_rate'] < 50) {
                $insights[] = ['type' => 'warning', 'message' => "Only {$stats['coverage_rate']}% of residents have recorded occupations."];
            }
            $topOcc = array_key_first($top);
            if ($topOcc) {
                $pct = $this->stats->percentage($top[$topOcc], $stats['total']);
                $insights[] = ['type' => 'info', 'message' => "\"{$topOcc}\" is the most common occupation with {$top[$topOcc]} residents ({$pct}%)."];
            }
        }

        return $insights;
    }
}
