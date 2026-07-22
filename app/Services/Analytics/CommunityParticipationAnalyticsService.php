<?php

namespace App\Services\Analytics;

use App\Models\Resident;
use Illuminate\Database\Eloquent\Builder;

class CommunityParticipationAnalyticsService
{
    public function __construct(protected StatisticsService $stats) {}

    protected function baseQuery(?int $villageId = null): Builder
    {
        $query = Resident::query()->where('status', 'active');
        if ($villageId) $query->where('village_id', $villageId);
        return $query;
    }

    public function getSummaryStats(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId);
        $total = $query->count();
        $withActivities = (clone $query)->whereNotNull('community_activities')->where('community_activities', '!=', '')->count();

        return [
            'total' => $total,
            'with_activities' => $withActivities,
            'participation_rate' => $this->stats->percentage($withActivities, $total),
        ];
    }

    public function getActivityDistribution(?int $villageId = null): array
    {
        $residents = $this->baseQuery($villageId)
            ->whereNotNull('community_activities')->where('community_activities', '!=', '')
            ->pluck('community_activities')->toArray();

        $counts = [];
        foreach ($residents as $text) {
            $items = array_map('trim', explode(',', $text));
            foreach ($items as $item) {
                if ($item !== '') {
                    $counts[$item] = ($counts[$item] ?? 0) + 1;
                }
            }
        }
        arsort($counts);
        return $this->stats->frequencyCount($counts);
    }

    public function getTopActivities(?int $villageId = null, int $limit = 10): array
    {
        return array_slice($this->getActivityDistribution($villageId), 0, $limit, true);
    }

    public function getVillageComparison(): array
    {
        $villages = \App\Models\Village::where('status', 'active')->get();
        $result = [];
        foreach ($villages as $v) {
            $stats = $this->getSummaryStats($v->id);
            $top = $this->getTopActivities($v->id, 3);
            $result[$v->village_name] = [
                'village_id' => $v->id, 'village_code' => $v->village_code,
                'total' => $stats['total'], 'active' => $stats['with_activities'],
                'rate' => $stats['participation_rate'], 'top_activities' => $top,
            ];
        }
        return $result;
    }

    public function getInsights(?int $villageId = null): array
    {
        $stats = $this->getSummaryStats($villageId);
        $top = $this->getTopActivities($villageId, 3);
        $insights = [];
        if ($stats['total'] > 0) {
            if ($stats['participation_rate'] > 50) {
                $insights[] = ['type' => 'success', 'message' => "Strong community participation at {$stats['participation_rate']}%."];
            } elseif ($stats['participation_rate'] < 20) {
                $insights[] = ['type' => 'warning', 'message' => "Community participation is only {$stats['participation_rate']}% — encourage more involvement."];
            }
            $topAct = array_key_first($top);
            if ($topAct) {
                $insights[] = ['type' => 'info', 'message' => "\"{$topAct}\" is the most common community activity."];
            }
        }
        return $insights;
    }
}
