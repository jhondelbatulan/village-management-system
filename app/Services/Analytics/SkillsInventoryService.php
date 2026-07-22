<?php

namespace App\Services\Analytics;

use App\Models\Resident;
use Illuminate\Database\Eloquent\Builder;

class SkillsInventoryService
{
    private const SKILL_CATEGORIES = [
        'IT & Technology' => ['Programming', 'Computer Technician', 'Graphic Design', 'Web Development', 'IT Support', 'Software Development', 'Data Entry', 'Network Administration'],
        'Education & Training' => ['Teaching', 'Education', 'Tutoring', 'Training', 'Mentoring', 'Childcare'],
        'Healthcare' => ['Nursing', 'Caregiving', 'Midwifery', 'Medical', 'Health Worker', 'First Aid', 'Dental'],
        'Construction & Trades' => ['Carpentry', 'Construction', 'Electrical', 'Plumbing', 'Welding', 'Masonry', 'Painting', 'Roofing', 'Tiling'],
        'Agriculture & Livestock' => ['Agriculture', 'Farming', 'Livestock', 'Gardening', 'Poultry', 'Rice Farming', 'Crop Management'],
        'Fishing & Aquatic' => ['Fishing', 'Aquaculture', 'Fish Processing', 'Boat Repair'],
        'Transportation' => ['Driving', 'Transportation', 'Logistics', 'Delivery', 'Mechanic'],
        'Business & Commerce' => ['Business', 'Entrepreneurship', 'Vending', 'Trading', 'Accounting', 'Marketing', 'Sales'],
        'Food & Hospitality' => ['Cooking', 'Baking', 'Food Processing', 'Catering', 'Restaurant', 'Hospitality'],
        'Creative & Arts' => ['Sewing', 'Tailoring', 'Embroidery', 'Weaving', 'Crafts', 'Art', 'Music', 'Photography'],
        'Service Industry' => ['Barbering', 'Hairdressing', 'Laundry', 'Cleaning', 'Security', 'Housekeeping'],
    ];

    public function __construct(protected StatisticsService $stats) {}

    protected function baseQuery(?int $villageId = null): Builder
    {
        $query = Resident::query()->where('status', 'active');
        if ($villageId) $query->where('village_id', $villageId);
        return $query;
    }

    public function getCategoryBreakdown(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId);
        $allSkills = (clone $query)->select('primary_skill', 'secondary_skill', 'skills')
            ->where(function ($q) {
                $q->whereNotNull('primary_skill')->where('primary_skill', '!=', '')
                  ->orWhereNotNull('secondary_skill')->where('secondary_skill', '!=', '')
                  ->orWhereNotNull('skills')->where('skills', '!=', '');
            })
            ->get();

        $skillTexts = [];
        foreach ($allSkills as $r) {
            foreach (['primary_skill', 'secondary_skill', 'skills'] as $field) {
                if ($r->$field) $skillTexts[] = $r->$field;
            }
        }

        $result = [];
        foreach (self::SKILL_CATEGORIES as $category => $keywords) {
            $count = 0;
            foreach ($skillTexts as $text) {
                foreach ($keywords as $kw) {
                    if (stripos($text, $kw) !== false) {
                        $count++;
                        break;
                    }
                }
            }
            if ($count > 0) {
                $result[$category] = $count;
            }
        }
        arsort($result);
        return $result;
    }

    public function getCategorySummary(?int $villageId = null): array
    {
        $breakdown = $this->getCategoryBreakdown($villageId);
        $total = array_sum($breakdown);
        $result = [];
        foreach ($breakdown as $cat => $count) {
            $result[$cat] = [
                'count' => $count,
                'percentage' => $this->stats->percentage($count, max($total, 1)),
            ];
        }
        return $result;
    }

    public function getSkillsByVillage(): array
    {
        $villages = \App\Models\Village::where('status', 'active')->get();
        $result = [];
        foreach ($villages as $v) {
            $categories = $this->getCategoryBreakdown($v->id);
            $total = array_sum($categories);
            $result[$v->village_name] = [
                'village_id' => $v->id,
                'village_code' => $v->village_code,
                'categories' => $categories,
                'total_skill_residents' => $total,
            ];
        }
        return $result;
    }

    public function getVillageCategoryComparison(): array
    {
        $villages = \App\Models\Village::where('status', 'active')->get();
        $allCategories = [];
        $result = [];

        foreach ($villages as $v) {
            $cats = $this->getCategoryBreakdown($v->id);
            $result[$v->village_name] = $cats;
            $allCategories = array_merge($allCategories, array_keys($cats));
        }

        $allCategories = array_unique($allCategories);
        return [
            'villages' => array_keys($result),
            'categories' => $allCategories,
            'data' => $result,
        ];
    }

    public function getInsights(?int $villageId = null): array
    {
        $summary = $this->getCategorySummary($villageId);
        $insights = [];

        if (count($summary) > 0) {
            $top = reset($summary);
            $topKey = array_key_first($summary);
            $insights[] = ['type' => 'info', 'message' => "\"{$topKey}\" is the most common skill category with {$top['count']} residents ({$top['percentage']}%)."];

            $totalSkillResidents = array_sum(array_column($summary, 'count'));
            $insights[] = ['type' => 'info', 'message' => "Total skill-identified residents: {$totalSkillResidents} across " . count($summary) . " categories."];

            if (count($summary) < 3) {
                $insights[] = ['type' => 'warning', 'message' => 'Limited skill diversity detected. Consider skills training programs.'];
            }
        }

        return $insights;
    }
}
