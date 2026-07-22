<?php

namespace App\Services\Analytics;

use App\Models\Resident;
use Illuminate\Database\Eloquent\Builder;

class CertificationAnalyticsService
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
        $withQualifications = (clone $query)->whereNotNull('special_qualifications')->where('special_qualifications', '!=', '')->count();

        return [
            'total' => $total,
            'with_qualifications' => $withQualifications,
            'qualification_rate' => $this->stats->percentage($withQualifications, $total),
        ];
    }

    public function getCertificationDistribution(?int $villageId = null): array
    {
        $residents = $this->baseQuery($villageId)
            ->whereNotNull('special_qualifications')->where('special_qualifications', '!=', '')
            ->pluck('special_qualifications')->toArray();

        $certCounts = [];
        foreach ($residents as $text) {
            $items = array_map('trim', explode(',', $text));
            foreach ($items as $item) {
                if ($item !== '') {
                    $certCounts[$item] = ($certCounts[$item] ?? 0) + 1;
                }
            }
        }
        arsort($certCounts);
        return $this->stats->frequencyCount($certCounts);
    }

    public function getTopCertifications(?int $villageId = null, int $limit = 10): array
    {
        return array_slice($this->getCertificationDistribution($villageId), 0, $limit, true);
    }

    public function getVillageComparison(): array
    {
        $villages = \App\Models\Village::where('status', 'active')->get();
        $result = [];
        foreach ($villages as $v) {
            $stats = $this->getSummaryStats($v->id);
            $top = $this->getTopCertifications($v->id, 3);
            $result[$v->village_name] = [
                'village_id' => $v->id, 'village_code' => $v->village_code,
                'total' => $stats['total'], 'qualified' => $stats['with_qualifications'],
                'rate' => $stats['qualification_rate'], 'top_certs' => $top,
            ];
        }
        return $result;
    }

    public function getInsights(?int $villageId = null): array
    {
        $stats = $this->getSummaryStats($villageId);
        $top = $this->getTopCertifications($villageId, 3);
        $insights = [];
        if ($stats['total'] > 0) {
            if ($stats['qualification_rate'] > 0) {
                $insights[] = ['type' => 'info', 'message' => "{$stats['with_qualifications']} residents ({$stats['qualification_rate']}%) have recorded qualifications."];
            }
            $topCert = array_key_first($top);
            if ($topCert) {
                $insights[] = ['type' => 'success', 'message' => "\"{$topCert}\" is the most common qualification."];
            }
        }
        return $insights;
    }
}
