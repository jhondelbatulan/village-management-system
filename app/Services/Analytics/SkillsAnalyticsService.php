<?php

namespace App\Services\Analytics;

use App\Models\Resident;
use Illuminate\Database\Eloquent\Builder;

class SkillsAnalyticsService
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
        $withPrimary = (clone $query)->whereNotNull('primary_skill')->where('primary_skill', '!=', '')->count();
        $withSecondary = (clone $query)->whereNotNull('secondary_skill')->where('secondary_skill', '!=', '')->count();
        $withAnySkill = (clone $query)->where(function ($q) {
            $q->whereNotNull('primary_skill')->where('primary_skill', '!=', '')
              ->orWhereNotNull('secondary_skill')->where('secondary_skill', '!=', '')
              ->orWhereNotNull('skills')->where('skills', '!=', '');
        })->count();

        return [
            'total' => $total,
            'with_primary' => $withPrimary,
            'with_secondary' => $withSecondary,
            'with_any_skill' => $withAnySkill,
            'skill_coverage' => $this->stats->percentage($withAnySkill, $total),
        ];
    }

    public function getPrimarySkillDistribution(?int $villageId = null): array
    {
        $counts = $this->baseQuery($villageId)
            ->selectRaw('primary_skill, COUNT(*) as count')
            ->whereNotNull('primary_skill')->where('primary_skill', '!=', '')
            ->groupBy('primary_skill')->orderByDesc('count')
            ->pluck('count', 'primary_skill')->toArray();
        return $this->stats->frequencyCount($counts);
    }

    public function getSecondarySkillDistribution(?int $villageId = null): array
    {
        $counts = $this->baseQuery($villageId)
            ->selectRaw('secondary_skill, COUNT(*) as count')
            ->whereNotNull('secondary_skill')->where('secondary_skill', '!=', '')
            ->groupBy('secondary_skill')->orderByDesc('count')
            ->pluck('count', 'secondary_skill')->toArray();
        return $this->stats->frequencyCount($counts);
    }

    public function getCombinedSkillDistribution(?int $villageId = null): array
    {
        $primary = $this->getPrimarySkillDistribution($villageId);
        $secondary = $this->getSecondarySkillDistribution($villageId);
        $combined = [];
        foreach ($primary as $skill => $data) {
            $combined[$skill] = ($combined[$skill] ?? 0) + $data['count'];
        }
        foreach ($secondary as $skill => $data) {
            $combined[$skill] = ($combined[$skill] ?? 0) + $data['count'];
        }
        arsort($combined);
        return $this->stats->frequencyCount($combined);
    }

    public function getTopSkills(?int $villageId = null, int $limit = 10): array
    {
        return $this->getCombinedSkillDistribution($villageId);
    }

    public function getSkillsByVillage(): array
    {
        $villages = \App\Models\Village::where('status', 'active')->get();
        $result = [];
        foreach ($villages as $v) {
            $dist = $this->getPrimarySkillDistribution($v->id);
            $result[$v->village_name] = [
                'village_id' => $v->id, 'village_code' => $v->village_code,
                'skills' => $dist,
                'total_with_skill' => array_sum(array_column($dist, 'count')),
            ];
        }
        return $result;
    }

    public function getSkillRankings(?int $villageId = null): array
    {
        $dist = $this->getCombinedSkillDistribution($villageId);
        $rank = 1;
        $result = [];
        foreach ($dist as $skill => $count) {
            $result[] = ['rank' => $rank++, 'skill' => $skill, 'count' => $count];
        }
        return $result;
    }

    public function getSkillsInventory(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId);
        $categories = [
            'IT Professionals' => ['Programming', 'Computer Technician', 'Graphic Design', 'Web Development', 'IT Support'],
            'Teachers' => ['Teaching', 'Education', 'Tutoring'],
            'Medical Workers' => ['Nursing', 'Caregiving', 'Midwifery', 'Medical', 'Health Worker'],
            'Construction Workers' => ['Carpentry', 'Construction', 'Electrical', 'Plumbing', 'Welding', 'Masonry', 'Painting'],
            'Farmers' => ['Agriculture', 'Farming', 'Livestock', 'Gardening'],
            'Fishermen' => ['Fishing', 'Aquaculture'],
            'Drivers' => ['Driving', 'Transportation'],
            'Entrepreneurs' => ['Business', 'Entrepreneurship', 'Vending', 'Trading'],
        ];

        $result = [];
        foreach ($categories as $category => $skills) {
            $count = (clone $query)->where(function ($q) use ($skills) {
                foreach ($skills as $skill) {
                    $q->orWhere('primary_skill', 'like', "%{$skill}%")
                      ->orWhere('secondary_skill', 'like', "%{$skill}%")
                      ->orWhere('skills', 'like', "%{$skill}%");
                }
            })->count();
            $result[$category] = $count;
        }
        return $result;
    }

    public function getInsights(?int $villageId = null): array
    {
        $stats = $this->getSummaryStats($villageId);
        $top = $this->getTopSkills($villageId, 5);
        $insights = [];
        if ($stats['total'] > 0) {
            if ($stats['skill_coverage'] > 0) {
                $insights[] = ['type' => 'info', 'message' => "{$stats['with_any_skill']} residents ({$stats['skill_coverage']}%) have recorded skills."];
            }
            $topSkill = array_key_first($top);
            if ($topSkill) {
                $insights[] = ['type' => 'success', 'message' => "\"{$topSkill}\" is the most common skill with {$top[$topSkill]} residents."];
            }
        }
        return $insights;
    }
}
