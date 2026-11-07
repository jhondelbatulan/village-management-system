<?php

namespace App\Services\Analytics;

use App\Models\Household;
use App\Models\Resident;
use App\Models\Village;
use App\Support\SqlHelper;
use Illuminate\Support\Facades\DB;

class HouseholdAnalyticsService
{
    public function __construct(
        protected StatisticsService $stats
    ) {}

    public function getSystemStats(): array
    {
        $total = Household::count();
        $approved = Household::where('approval_status', 'approved')->count();
        $pending = Household::whereIn('approval_status', ['pending', 'admin_approved'])->count();
        $rejected = Household::where('approval_status', 'rejected')->count();
        $draft = Household::where('approval_status', 'draft')->count();
        $active = Household::where('status', 'active')->count();
        $inactive = Household::where('status', 'inactive')->count();
        $previousMonth = Household::where('created_at', '<=', now()->subMonth()->endOfMonth())->count();
        $thisMonth = Household::where('created_at', '>=', now()->startOfMonth())->count();

        return [
            'total' => $total,
            'approved' => $approved,
            'pending' => $pending,
            'rejected' => $rejected,
            'draft' => $draft,
            'active' => $active,
            'inactive' => $inactive,
            'previous_month' => $previousMonth,
            'previous' => $previousMonth,
            'this_month' => $thisMonth,
            'change' => $this->stats->growthRate($thisMonth, $previousMonth),
        ];
    }

    public function getVillageStats(int $villageId): array
    {
        $total = Household::where('village_id', $villageId)->count();
        $approved = Household::where('village_id', $villageId)->where('approval_status', 'approved')->count();
        $pending = Household::where('village_id', $villageId)->whereIn('approval_status', ['pending', 'admin_approved'])->count();
        $rejected = Household::where('village_id', $villageId)->where('approval_status', 'rejected')->count();
        $draft = Household::where('village_id', $villageId)->where('approval_status', 'draft')->count();
        $active = Household::where('village_id', $villageId)->where('status', 'active')->count();
        $inactive = Household::where('village_id', $villageId)->where('status', 'inactive')->count();
        $previousMonth = Household::where('village_id', $villageId)
            ->where('created_at', '<=', now()->subMonth()->endOfMonth())->count();
        $thisMonth = Household::where('village_id', $villageId)
            ->where('created_at', '>=', now()->startOfMonth())->count();

        $mappedHouseholds = Household::where('village_id', $villageId)
            ->where('status', 'active')
            ->whereNotNull('latitude')->whereNotNull('longitude')->count();

        $totalBlocks = Household::where('village_id', $villageId)
            ->whereNotNull('block_number')->distinct('block_number')->count('block_number');

        $totalLots = Household::where('village_id', $villageId)
            ->whereNotNull('lot_number')->count();

        return [
            'total' => $total,
            'approved' => $approved,
            'pending' => $pending,
            'rejected' => $rejected,
            'draft' => $draft,
            'active' => $active,
            'inactive' => $inactive,
            'previous_month' => $previousMonth,
            'previous' => $previousMonth,
            'this_month' => $thisMonth,
            'change' => $this->stats->growthRate($thisMonth, $previousMonth),
            'accuracy_pct' => $total > 0 ? round(($active / $total) * 100, 1) : 0,
            'mapped_households' => $mappedHouseholds,
            'coverage_pct' => $active > 0 ? round(($mappedHouseholds / $active) * 100, 1) : 0,
            'total_blocks' => $totalBlocks,
            'total_lots' => $totalLots,
        ];
    }

    public function getStatusDistribution(?int $villageId = null): array
    {
        $query = Household::query();
        if ($villageId) {
            $query->where('village_id', $villageId);
        }

        $counts = $query->selectRaw('
            COALESCE(approval_status, \'draft\') as approval_status_label,
            COUNT(*) as count
        ')->groupByRaw('COALESCE(approval_status, \'draft\')')->pluck('count', 'approval_status_label')->toArray();

        $total = array_sum($counts);

        return [
            'data' => $this->stats->frequencyCount($counts),
            'total' => $total,
            'labels' => array_keys($counts),
            'values' => array_values($counts),
        ];
    }

    public function getSizeDistribution(?int $villageId = null): array
    {
        $query = Household::withCount(['residents' => fn ($q) => $q->where('status', 'active')]);
        if ($villageId) {
            $query->where('village_id', $villageId);
        }

        $sizes = $query->get()->pluck('residents_count')->filter()->toArray();

        $distribution = [
            '1' => 0,
            '2' => 0,
            '3' => 0,
            '4' => 0,
            '5' => 0,
            '6' => 0,
            '7' => 0,
            '8+' => 0,
        ];

        foreach ($sizes as $size) {
            if ($size >= 8) {
                $distribution['8+']++;
            } else {
                $key = (string) $size;
                if (isset($distribution[$key])) {
                    $distribution[$key]++;
                }
            }
        }

        $total = count($sizes);
        $avgSize = $total > 0 ? round(array_sum($sizes) / $total, 2) : 0;
        $sortedSizes = $sizes;
        sort($sortedSizes);
        $largest = ! empty($sortedSizes) ? max($sortedSizes) : 0;
        $smallest = ! empty($sortedSizes) ? min($sortedSizes) : 0;

        return [
            'data' => $this->stats->frequencyCount($distribution),
            'total' => $total,
            'labels' => array_keys($distribution),
            'values' => array_values($distribution),
            'average_size' => $avgSize,
            'largest' => $largest,
            'smallest' => $smallest,
            'median' => $this->stats->median($sizes),
        ];
    }

    public function getCompositionDistribution(?int $villageId = null): array
    {
        $query = Resident::where('status', 'active');
        if ($villageId) {
            $query->where('village_id', $villageId);
        }

        $counts = $query->selectRaw('
            COALESCE(relationship_to_head, \'Other\') as relationship,
            COUNT(*) as count
        ')->groupBy('relationship')->pluck('count', 'relationship')->toArray();

        $total = array_sum($counts);

        return [
            'data' => $this->stats->frequencyCount($counts),
            'total' => $total,
            'labels' => array_keys($counts),
            'values' => array_values($counts),
        ];
    }

    public function getBlockDistribution(?int $villageId = null): array
    {
        $query = Household::whereNotNull('block_number');
        if ($villageId) {
            $query->where('village_id', $villageId);
        }

        $counts = $query->selectRaw('block_number, COUNT(*) as count')
            ->groupBy('block_number')
            ->orderBy('block_number')
            ->pluck('count', 'block_number')
            ->toArray();

        $totalBlocks = count($counts);
        $totalHouseholds = array_sum($counts);

        return [
            'data' => $this->stats->frequencyCount($counts),
            'total_blocks' => $totalBlocks,
            'total_households' => $totalHouseholds,
            'labels' => array_keys($counts),
            'values' => array_values($counts),
        ];
    }

    public function getLotDistribution(?int $villageId = null): array
    {
        $query = Household::whereNotNull('block_number')->whereNotNull('lot_number');
        if ($villageId) {
            $query->where('village_id', $villageId);
        }

        $counts = $query->selectRaw('
            CONCAT(block_number, \'-\', lot_number) as block_lot,
            COUNT(*) as count
        ')->groupBy('block_lot')
            ->pluck('count', 'block_lot')
            ->toArray();

        $totalLots = count($counts);

        return [
            'total_lots' => $totalLots,
            'labels' => array_keys($counts),
            'values' => array_values($counts),
        ];
    }

    public function getBlockLotStats(?int $villageId = null): array
    {
        $query = Household::query();
        if ($villageId) {
            $query->where('village_id', $villageId);
        }

        $totalBlocks = (clone $query)->whereNotNull('block_number')
            ->distinct('block_number')->count('block_number');

        $totalLots = (clone $query)->count();

        $occupiedLots = (clone $query)
            ->whereNotNull('block_number')
            ->where('status', 'active')
            ->count();

        $availableLots = $totalLots - $occupiedLots;

        return [
            'total_blocks' => $totalBlocks,
            'total_lots' => $totalLots,
            'occupied_lots' => $occupiedLots,
            'available_lots' => max(0, $availableLots),
        ];
    }

    public function getOccupancyStats(?int $villageId = null): array
    {
        $query = Household::withCount(['residents' => fn ($q) => $q->where('status', 'active')]);
        if ($villageId) {
            $query->where('village_id', $villageId);
        }

        $households = $query->get();
        $occupancies = $households->pluck('residents_count')->filter()->toArray();

        $avgOccupancy = ! empty($occupancies) ? round(array_sum($occupancies) / count($occupancies), 2) : 0;
        $highestOccupancy = ! empty($occupancies) ? max($occupancies) : 0;
        $lowestOccupancy = ! empty($occupancies) ? min($occupancies) : 0;
        $totalResidents = array_sum($occupancies);
        $totalHouseholds = count($households);

        $densityPerBlock = [];

        $blockQuery = DB::table('households')
            ->join('residents', function ($join) {
                $join->on('households.id', '=', 'residents.household_id')
                    ->where('residents.status', '=', 'active')
                    ->whereNull('residents.deleted_at');
            })
            ->whereNotNull('households.block_number')
            ->whereNull('households.deleted_at');

        if ($villageId) {
            $blockQuery->where('households.village_id', $villageId);
        }

        $blockData = $blockQuery
            ->select('households.block_number', DB::raw('COUNT(DISTINCT households.id) as household_count, COUNT(residents.id) as resident_count'))
            ->groupBy('households.block_number')
            ->get();

        foreach ($blockData as $block) {
            $densityPerBlock[$block->block_number] = [
                'households' => $block->household_count,
                'residents' => $block->resident_count,
                'avg_per_household' => $block->household_count > 0
                    ? round($block->resident_count / $block->household_count, 2)
                    : 0,
            ];
        }

        return [
            'average_occupancy' => $avgOccupancy,
            'highest_occupancy' => $highestOccupancy,
            'lowest_occupancy' => $lowestOccupancy,
            'total_residents' => $totalResidents,
            'total_households' => $totalHouseholds,
            'average_residents_per_household' => $avgOccupancy,
            'density_per_block' => $densityPerBlock,
        ];
    }

    public function getMonthlyTrend(?int $villageId = null, int $months = 12): array
    {
        $query = Household::where('created_at', '>=', now()->subMonths($months)->startOfMonth());
        if ($villageId) {
            $query->where('village_id', $villageId);
        }

        $data = $query->selectRaw(
            SqlHelper::dateFormat('created_at', '%Y-%m').' as month, COUNT(*) as count'
        )->groupBy('month')->pluck('count', 'month')->toArray();

        $result = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $result[$key] = $data[$key] ?? 0;
        }

        return $result;
    }

    public function getYearlyTrend(?int $villageId = null, int $years = 5): array
    {
        $query = Household::where('created_at', '>=', now()->subYears($years)->startOfYear());
        if ($villageId) {
            $query->where('village_id', $villageId);
        }

        $data = $query->selectRaw(
            SqlHelper::dateFormat('created_at', '%Y').' as year, COUNT(*) as count'
        )->groupBy('year')->pluck('count', 'year')->toArray();

        $result = [];
        for ($i = $years - 1; $i >= 0; $i--) {
            $key = now()->subYears($i)->format('Y');
            $result[$key] = $data[$key] ?? 0;
        }

        return $result;
    }

    public function getTypeDistribution(?int $villageId = null): array
    {
        $query = Household::where('status', 'active');
        if ($villageId) {
            $query->where('village_id', $villageId);
        }

        $counts = $query->selectRaw('
            COALESCE(household_type, \'Unspecified\') as type,
            COUNT(*) as count
        ')->groupBy('type')->pluck('count', 'type')->toArray();

        $total = array_sum($counts);

        return [
            'data' => $this->stats->frequencyCount($counts),
            'total' => $total,
            'labels' => array_keys($counts),
            'values' => array_values($counts),
        ];
    }

    public function getVillageComparison(): array
    {
        $villages = Village::where('status', 'active')->get();

        return $villages->map(function ($village) {
            $stats = $this->getVillageStats($village->id);
            $sizeDist = $this->getSizeDistribution($village->id);
            $occupancy = $this->getOccupancyStats($village->id);

            return [
                'village' => $village,
                'total' => $stats['total'],
                'approved' => $stats['approved'],
                'pending' => $stats['pending'],
                'rejected' => $stats['rejected'],
                'active' => $stats['active'],
                'change' => $stats['change'],
                'total_blocks' => $stats['total_blocks'],
                'total_lots' => $stats['total_lots'],
                'average_size' => $sizeDist['average_size'],
                'average_occupancy' => $occupancy['average_occupancy'],
            ];
        })->toArray();
    }

    public function getHouseholdSummary(?int $villageId = null): array
    {
        $stats = $this->getSystemStats();
        if ($villageId) {
            $stats = $this->getVillageStats($villageId);
        }

        $blockLot = $this->getBlockLotStats($villageId);
        $sizeDist = $this->getSizeDistribution($villageId);
        $occupancy = $this->getOccupancyStats($villageId);

        return [
            'total_households' => $stats['total'],
            'approved' => $stats['approved'],
            'pending' => $stats['pending'],
            'rejected' => $stats['rejected'],
            'draft' => $stats['draft'] ?? 0,
            'active' => $stats['active'],
            'inactive' => $stats['inactive'],
            'total_blocks' => $blockLot['total_blocks'],
            'total_lots' => $blockLot['total_lots'],
            'occupied_lots' => $blockLot['occupied_lots'],
            'available_lots' => $blockLot['available_lots'],
            'average_size' => $sizeDist['average_size'],
            'largest_household' => $sizeDist['largest'],
            'smallest_household' => $sizeDist['smallest'],
            'median_size' => $sizeDist['median'],
            'average_occupancy' => $occupancy['average_occupancy'],
            'highest_occupancy' => $occupancy['highest_occupancy'],
            'lowest_occupancy' => $occupancy['lowest_occupancy'],
            'monthly_growth' => $stats['change'],
        ];
    }

    public function getHouseholdInsights(?int $villageId = null): array
    {
        $insights = [];
        $summary = $this->getHouseholdSummary($villageId);
        $sizeDist = $this->getSizeDistribution($villageId);
        $composition = $this->getCompositionDistribution($villageId);
        $occupancy = $this->getOccupancyStats($villageId);
        $blockDist = $this->getBlockDistribution($villageId);

        if ($summary['average_size'] > 0) {
            $insights[] = [
                'type' => 'info',
                'icon' => 'bi-house-door',
                'message' => "The average household size is {$summary['average_size']} members.",
            ];
        }

        if ($summary['average_occupancy'] > 0) {
            $insights[] = [
                'type' => 'info',
                'icon' => 'bi-people',
                'message' => "Average residents per household is {$summary['average_occupancy']}.",
            ];
        }

        $largestHH = $sizeDist['data'] ?? [];
        $maxSize = 0;
        $maxLabel = '';
        foreach ($largestHH as $item) {
            if ($item['count'] > $maxSize) {
                $maxSize = $item['count'];
                $maxLabel = $item['label'];
            }
        }
        if ($maxLabel) {
            $insights[] = [
                'type' => 'primary',
                'icon' => 'bi-bar-chart',
                'message' => "Most households consist of {$maxLabel} member(s).",
            ];
        }

        if (! empty($blockDist['labels'])) {
            $blockValues = $blockDist['values'];
            $maxBlockKey = array_search(max($blockValues), $blockValues);
            $maxBlockLabel = $blockDist['labels'][$maxBlockKey] ?? '';
            if ($maxBlockLabel) {
                $insights[] = [
                    'type' => 'warning',
                    'icon' => 'bi-grid-3x3',
                    'message' => "Block {$maxBlockLabel} has the highest household count.",
                ];
            }
        }

        if ($summary['pending'] > 0) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'bi-hourglass-split',
                'message' => "{$summary['pending']} household registration(s) pending approval.",
            ];
        }

        if ($summary['available_lots'] > 0) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'bi-plus-circle',
                'message' => "{$summary['available_lots']} available lot(s) for new households.",
            ];
        }

        if ($summary['total_households'] > 0) {
            $approvalRate = round(($summary['approved'] / $summary['total_households']) * 100, 1);
            $insights[] = [
                'type' => 'success',
                'icon' => 'bi-check-circle',
                'message' => "Household approval rate is {$approvalRate}%.",
            ];
        }

        if (($summary['monthly_growth'] ?? 0) > 0) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'bi-graph-up',
                'message' => "Household registrations increased by {$summary['monthly_growth']}% this month.",
            ];
        } elseif (($summary['monthly_growth'] ?? 0) < 0) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'bi-graph-down',
                'message' => 'Household registrations decreased by '.abs($summary['monthly_growth']).'% this month.',
            ];
        }

        $totalResidents = $occupancy['total_residents'] ?? 0;
        $totalHouseholds = $occupancy['total_households'] ?? 0;
        if ($totalHouseholds > 0 && $totalResidents > 0) {
            $density = round($totalResidents / $totalHouseholds, 1);
            $insights[] = [
                'type' => 'info',
                'icon' => 'bi-people-fill',
                'message' => "There are {$totalResidents} residents across {$totalHouseholds} households (density: {$density}).",
            ];
        }

        return $insights;
    }

    public function getGrowthAnalysis(?int $villageId = null): array
    {
        $currentMonth = now()->format('Y-m');
        $previousMonth = now()->subMonth()->format('Y-m');

        $currentCount = Household::query()
            ->when($villageId, fn ($q) => $q->where('village_id', $villageId))
            ->whereRaw(SqlHelper::dateFormat('created_at', '%Y-%m').' = ?', [$currentMonth])
            ->count();

        $previousCount = Household::query()
            ->when($villageId, fn ($q) => $q->where('village_id', $villageId))
            ->whereRaw(SqlHelper::dateFormat('created_at', '%Y-%m').' = ?', [$previousMonth])
            ->count();

        $thisWeekCount = Household::query()
            ->when($villageId, fn ($q) => $q->where('village_id', $villageId))
            ->where('created_at', '>=', now()->startOfWeek())
            ->count();

        $lastWeekCount = Household::query()
            ->when($villageId, fn ($q) => $q->where('village_id', $villageId))
            ->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])
            ->count();

        $thisQuarterCount = Household::query()
            ->when($villageId, fn ($q) => $q->where('village_id', $villageId))
            ->where('created_at', '>=', now()->startOfQuarter())
            ->count();

        $thisYearCount = Household::query()
            ->when($villageId, fn ($q) => $q->where('village_id', $villageId))
            ->where('created_at', '>=', now()->startOfYear())
            ->count();

        $previousYearCount = Household::query()
            ->when($villageId, fn ($q) => $q->where('village_id', $villageId))
            ->whereBetween('created_at', [now()->subYear()->startOfYear(), now()->subYear()->endOfYear()])
            ->count();

        return [
            'weekly' => [
                'current' => $thisWeekCount,
                'previous' => $lastWeekCount,
                'change' => $thisWeekCount - $lastWeekCount,
                'growth_rate' => $this->stats->growthRate($thisWeekCount, $lastWeekCount),
            ],
            'monthly' => [
                'current' => $currentCount,
                'previous' => $previousCount,
                'change' => $currentCount - $previousCount,
                'growth_rate' => $this->stats->growthRate($currentCount, $previousCount),
            ],
            'quarterly' => [
                'current' => $thisQuarterCount,
            ],
            'yearly' => [
                'current' => $thisYearCount,
                'previous' => $previousYearCount,
                'change' => $thisYearCount - $previousYearCount,
                'growth_rate' => $this->stats->growthRate($thisYearCount, $previousYearCount),
            ],
        ];
    }

    public function getRegistrationStats(?int $villageId = null): array
    {
        $today = now()->toDateString();
        $weekAgo = now()->subWeek()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $yearStart = now()->startOfYear()->toDateString();

        $base = Household::query();
        if ($villageId) {
            $base->where('village_id', $villageId);
        }

        $todayCount = (clone $base)->whereDate('created_at', $today)->count();
        $weekCount = (clone $base)->where('created_at', '>=', $weekAgo)->count();
        $monthCount = (clone $base)->where('created_at', '>=', $monthStart)->count();
        $yearCount = (clone $base)->where('created_at', '>=', $yearStart)->count();

        $first = (clone $base)->oldest('created_at')->first();
        $months = $first ? max(1, $first->created_at->diffInMonths(now()) + 1) : 1;
        $total = (clone $base)->count();
        $avgMonthly = round($total / $months, 1);

        return [
            'today' => $todayCount,
            'this_week' => $weekCount,
            'this_month' => $monthCount,
            'this_year' => $yearCount,
            'avg_monthly' => $avgMonthly,
            'avg_annual' => round($avgMonthly * 12, 1),
        ];
    }
}
