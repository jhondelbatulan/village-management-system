<?php

namespace App\Services\Analytics;

use App\Models\Resident;
use Illuminate\Database\Eloquent\Builder;

class EducationAnalyticsService
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

    public function getSummaryStats(?int $villageId = null): array
    {
        $query = $this->baseQuery($villageId);
        $total = $query->count();

        $educated = (clone $query)->whereNotNull('educational_attainment')->where('educational_attainment', '!=', '')->count();
        $enrolled = (clone $query)->whereNotNull('education_level')->where('education_level', '!=', 'Not Enrolled')->where('education_level', '!=', '')->count();
        $scholars = (clone $query)->where('is_scholarship', true)->count();
        $students = (clone $query)->where('is_student', true)->count();

        return [
            'total_residents' => $total,
            'educated' => $educated,
            'education_rate' => $this->stats->percentage($educated, $total),
            'currently_enrolled' => $enrolled,
            'enrollment_rate' => $this->stats->percentage($enrolled, $total),
            'scholars' => $scholars,
            'scholarship_rate' => $this->stats->percentage($scholars, $total),
            'students' => $students,
            'student_rate' => $this->stats->percentage($students, $total),
        ];
    }

    public function getAttainmentDistribution(?int $villageId = null): array
    {
        $counts = $this->baseQuery($villageId)
            ->selectRaw('educational_attainment, COUNT(*) as count')
            ->whereNotNull('educational_attainment')
            ->where('educational_attainment', '!=', '')
            ->groupBy('educational_attainment')
            ->pluck('count', 'educational_attainment')
            ->toArray();

        ksort($counts);
        return $this->stats->frequencyCount($counts);
    }

    public function getEducationLevelDistribution(?int $villageId = null): array
    {
        $counts = $this->baseQuery($villageId)
            ->selectRaw('education_level, COUNT(*) as count')
            ->whereNotNull('education_level')
            ->where('education_level', '!=', '')
            ->groupBy('education_level')
            ->pluck('count', 'education_level')
            ->toArray();

        $order = ['Not Enrolled', 'Elementary', 'Junior High School', 'Senior High School', 'Vocational/Technical', 'College/University', 'Post Graduate'];
        $ordered = [];
        foreach ($order as $level) {
            if (isset($counts[$level])) {
                $ordered[$level] = $counts[$level];
            }
        }
        foreach ($counts as $k => $v) {
            if (!isset($ordered[$k])) {
                $ordered[$k] = $v;
            }
        }

        return $this->stats->frequencyCount($ordered);
    }

    public function getSchoolNameDistribution(?int $villageId = null): array
    {
        $counts = $this->baseQuery($villageId)
            ->selectRaw('school_name, COUNT(*) as count')
            ->whereNotNull('school_name')
            ->where('school_name', '!=', '')
            ->groupBy('school_name')
            ->orderByDesc('count')
            ->limit(15)
            ->pluck('count', 'school_name')
            ->toArray();

        return $this->stats->frequencyCount($counts);
    }

    public function getScholarshipByAttainment(?int $villageId = null): array
    {
        $rows = $this->baseQuery($villageId)
            ->selectRaw('educational_attainment, is_scholarship, COUNT(*) as count')
            ->whereNotNull('educational_attainment')
            ->where('educational_attainment', '!=', '')
            ->groupBy('educational_attainment', 'is_scholarship')
            ->get()
            ->keyBy(fn($r) => $r->educational_attainment . '_' . ($r->is_scholarship ? 'yes' : 'no'));

        $attainments = $this->getAttainmentDistribution($villageId);
        $result = [];

        foreach ($attainments as $att => $data) {
            $withScholar = (int) ($rows->get($att . '_yes')?->count ?? 0);
            $withoutScholar = (int) ($rows->get($att . '_no')?->count ?? 0);
            $total = $withScholar + $withoutScholar;
            $result[$att] = [
                'total' => $total,
                'with_scholarship' => $withScholar,
                'without_scholarship' => $withoutScholar,
                'scholarship_rate' => $this->stats->percentage($withScholar, $total),
            ];
        }

        return $result;
    }

    public function getVillageComparison(): array
    {
        $villages = \App\Models\Village::where('status', 'active')->get();
        $result = [];

        foreach ($villages as $village) {
            $stats = $this->getSummaryStats($village->id);
            $result[$village->village_name] = [
                'village_id' => $village->id,
                'village_code' => $village->village_code,
                'total' => $stats['total_residents'],
                'education_rate' => $stats['education_rate'],
                'enrollment_rate' => $stats['enrollment_rate'],
                'scholarship_rate' => $stats['scholarship_rate'],
                'student_rate' => $stats['student_rate'],
            ];
        }

        return $result;
    }

    public function getInsights(?int $villageId = null): array
    {
        $stats = $this->getSummaryStats($villageId);
        $insights = [];

        if ($stats['total_residents'] > 0) {
            if ($stats['education_rate'] >= 90) {
                $insights[] = ['type' => 'success', 'message' => "Excellent education rate of {$stats['education_rate']}%."];
            } elseif ($stats['education_rate'] < 50) {
                $insights[] = ['type' => 'warning', 'message' => "Education rate is only {$stats['education_rate']}% — consider literacy programs."];
            }

            if ($stats['scholarship_rate'] > 0) {
                $insights[] = ['type' => 'info', 'message' => "{$stats['scholars']} residents ({$stats['scholarship_rate']}%) are on scholarship."];
            }

            if ($stats['enrollment_rate'] < 30 && $stats['total_residents'] > 0) {
                $insights[] = ['type' => 'warning', 'message' => "Only {$stats['enrollment_rate']}% of residents are currently enrolled."];
            }

            if ($stats['student_rate'] > 0) {
                $insights[] = ['type' => 'info', 'message' => "{$stats['students']} active students in the community ({$stats['student_rate']}%)."];
            }
        }

        return $insights;
    }
}
