<?php

namespace App\Services;

use App\Models\Household;
use App\Models\Resident;
use App\Models\Village;
use Illuminate\Support\Facades\DB;

class GisService
{
    public function getVillageHouseholds(int $villageId)
    {
        return Household::where('village_id', $villageId)
            ->where('approval_status', 'approved')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('village:id,village_name,village_code,latitude,longitude')
            ->select([
                'id', 'household_number', 'household_head_name', 'latitude', 'longitude',
                'block_number', 'lot_number', 'household_type', 'status', 'approval_status',
                'address', 'purok', 'village_id', 'created_at', 'approved_at',
            ])
            ->withCount(['residents as member_count' => function ($q) {
                $q->where('status', 'active');
            }])
            ->orderBy('household_number')
            ->get();
    }

    public function getAllHouseholdsWithCoords()
    {
        return Household::where('approval_status', 'approved')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('village:id,village_name,village_code,latitude,longitude')
            ->select([
                'id', 'household_number', 'household_head_name', 'latitude', 'longitude',
                'block_number', 'lot_number', 'household_type', 'status', 'approval_status',
                'village_id',
            ])
            ->withCount(['residents as member_count' => function ($q) {
                $q->where('status', 'active');
            }])
            ->orderBy('village_id')
            ->orderBy('household_number')
            ->get();
    }

    public function getLatestMappedHouseholds(?int $villageId = null, int $limit = 10)
    {
        $builder = Household::where('approval_status', 'approved')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('village:id,village_name,village_code,latitude,longitude')
            ->select([
                'id', 'household_number', 'household_head_name', 'latitude', 'longitude',
                'block_number', 'lot_number', 'household_type', 'status', 'approval_status',
                'village_id', 'address', 'purok', 'created_at',
            ])
            ->withCount(['residents as member_count' => function ($q) {
                $q->where('status', 'active');
            }]);

        if ($villageId) {
            $builder->where('village_id', $villageId);
        }

        return $builder->latest('created_at')->limit($limit)->get();
    }

    public function getVillagesWithCoords()
    {
        return Village::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select([
                'id', 'village_name', 'village_code', 'latitude', 'longitude',
                'address', 'total_households', 'total_population',
            ])
            ->withCount([
                'households as household_count',
                'residents as resident_count' => function ($q) {
                    $q->where('status', 'active');
                },
            ])
            ->orderBy('village_name')
            ->get();
    }

    public function getAllVillages()
    {
        return Village::select(['id', 'village_name', 'village_code', 'latitude', 'longitude', 'address'])
            ->orderBy('village_name')
            ->get();
    }

    public function searchHouseholds(string $query, ?int $villageId = null)
    {
        $builder = Household::where('approval_status', 'approved')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('village:id,village_name,village_code,latitude,longitude');

        if ($villageId) {
            $builder->where('village_id', $villageId);
        }

        $builder->where(function ($q) use ($query) {
            $q->where('household_number', 'like', "%{$query}%")
                ->orWhere('household_head_name', 'like', "%{$query}%")
                ->orWhere('block_number', 'like', "%{$query}%")
                ->orWhere('lot_number', 'like', "%{$query}%")
                ->orWhere('address', 'like', "%{$query}%")
                ->orWhere('purok', 'like', "%{$query}%");
        });

        return $builder->select([
            'id', 'household_number', 'household_head_name', 'latitude', 'longitude',
            'block_number', 'lot_number', 'household_type', 'status', 'approval_status',
            'village_id', 'address', 'purok',
        ])->withCount(['residents as member_count' => function ($q) {
            $q->where('status', 'active');
        }])->get();
    }

    public function searchResidents(string $query, ?int $villageId = null)
    {
        $builder = Resident::whereNotNull('household_id')
            ->whereHas('household', function ($q) {
                $q->where('approval_status', 'approved')
                    ->whereNotNull('latitude')->whereNotNull('longitude');
            })
            ->with(['household' => function ($q) {
                $q->select([
                    'id', 'household_number', 'household_head_name', 'latitude', 'longitude',
                    'block_number', 'lot_number', 'household_type', 'status', 'approval_status',
                    'village_id', 'address', 'purok',
                ])->withCount(['residents as member_count' => function ($qq) {
                    $qq->where('status', 'active');
                }]);
            }]);

        if ($villageId) {
            $builder->where('village_id', $villageId);
        }

        $builder->where(function ($q) use ($query) {
            $q->where('first_name', 'like', "%{$query}%")
                ->orWhere('last_name', 'like', "%{$query}%")
                ->orWhere('resident_code', 'like', "%{$query}%");
        });

        return $builder->select([
            'id', 'first_name', 'last_name', 'resident_code', 'household_id', 'village_id',
        ])->get();
    }

    public function filterHouseholds(array $filters)
    {
        $builder = Household::where('approval_status', 'approved')
            ->with('village:id,village_name,village_code,latitude,longitude');

        if (! empty($filters['village_id'])) {
            $builder->where('village_id', $filters['village_id']);
        }
        if (! empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }
        if (! empty($filters['approval_status'])) {
            $builder->where('approval_status', $filters['approval_status']);
        }
        if (! empty($filters['household_type'])) {
            $builder->where('household_type', $filters['household_type']);
        }
        if (! empty($filters['block_number'])) {
            $builder->where('block_number', 'like', "%{$filters['block_number']}%");
        }
        if (! empty($filters['purok'])) {
            $builder->where('purok', 'like', "%{$filters['purok']}%");
        }

        $mapped = ! empty($filters['mapped']);
        $unmapped = ! empty($filters['unmapped']);

        if ($mapped && !$unmapped) {
            $builder->whereNotNull('latitude')->whereNotNull('longitude');
        } elseif ($unmapped && !$mapped) {
            $builder->where(function ($q) {
                $q->whereNull('latitude')->orWhereNull('longitude');
            });
        }

        return $builder->select([
            'id', 'household_number', 'household_head_name', 'latitude', 'longitude',
            'block_number', 'lot_number', 'household_type', 'status', 'approval_status',
            'village_id', 'address', 'purok',
        ])->withCount(['residents as member_count' => function ($q) {
            $q->where('status', 'active');
        }])->get();
    }

    public function getVillageStatistics(int $villageId): array
    {
        $total = Household::where('village_id', $villageId)->count();
        $mapped = Household::where('village_id', $villageId)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->count();
        $unmapped = $total - $mapped;

        $byType = Household::where('village_id', $villageId)
            ->select('household_type', DB::raw('count(*) as total'))
            ->groupBy('household_type')
            ->pluck('total', 'household_type');

        $byStatus = Household::where('village_id', $villageId)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $byApproval = Household::where('village_id', $villageId)
            ->select('approval_status', DB::raw('count(*) as total'))
            ->groupBy('approval_status')
            ->pluck('total', 'approval_status');

        $totalResidents = Resident::where('village_id', $villageId)->where('status', 'active')->count();
        $totalMembers = Household::where('village_id', $villageId)
            ->withCount(['residents as member_count' => function ($q) {
                $q->where('status', 'active');
            }])
            ->get()
            ->sum('member_count');

        $village = Village::find($villageId);
        $hasGisLocation = $village && $village->latitude && $village->longitude;

        return compact(
            'total', 'mapped', 'unmapped', 'byType', 'byStatus', 'byApproval',
            'totalResidents', 'totalMembers', 'hasGisLocation'
        );
    }

    public function getSystemStatistics(): array
    {
        $totalVillages = Village::count();
        $totalHouseholds = Household::count();
        $totalMapped = Household::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->count();
        $totalUnmapped = $totalHouseholds - $totalMapped;
        $totalResidents = Resident::where('status', 'active')->count();

        $approvedHouseholds = Household::where('approval_status', 'approved')->count();
        $pendingHouseholds = Household::where('approval_status', 'pending')->count();
        $rejectedHouseholds = Household::where('approval_status', 'rejected')->count();

        $approvedResidents = Resident::where('approval_status', 'approved')->count();
        $pendingResidents = Resident::where('approval_status', 'pending')->count();
        $rejectedResidents = Resident::where('approval_status', 'rejected')->count();

        $occupiedHouseholds = Household::where('status', 'active')->count();
        $vacantHouseholds = Household::where('status', 'inactive')->count();

        $totalBlocks = Household::distinct('block_number')->whereNotNull('block_number')->count('block_number');
        $totalLots = Household::distinct('lot_number')->whereNotNull('lot_number')->count('lot_number');

        $coverage = $totalHouseholds > 0 ? round(($totalMapped / $totalHouseholds) * 100, 1) : 0;

        $lastHouseholdAdded = Household::latest('created_at')->value('created_at');
        $lastResidentAdded = Resident::latest('created_at')->value('created_at');

        $recentActivity = collect();

        $recentVillageUpdates = Village::whereNotNull('latitude')
            ->where('updated_at', '>=', now()->subDays(30))
            ->latest('updated_at')
            ->limit(5)
            ->get(['id', 'village_name', 'updated_at'])
            ->map(fn ($v) => [
                'user' => 'System',
                'action' => "Village Location Updated: {$v->village_name}",
                'date' => $v->updated_at,
            ]);

        $recentHouseholdApproved = Household::where('approval_status', 'approved')
            ->where('approved_at', '>=', now()->subDays(30))
            ->latest('approved_at')
            ->limit(5)
            ->get(['id', 'household_number', 'approved_at'])
            ->map(fn ($h) => [
                'user' => 'Admin',
                'action' => "Household Approved: {$h->household_number}",
                'date' => $h->approved_at,
            ]);

        $recentActivity = $recentVillageUpdates->concat($recentHouseholdApproved)
            ->sortByDesc('date')
            ->take(5)
            ->values();

        return compact(
            'totalVillages', 'totalHouseholds', 'totalMapped', 'totalUnmapped',
            'totalResidents', 'approvedHouseholds', 'pendingHouseholds', 'rejectedHouseholds',
            'approvedResidents', 'pendingResidents', 'rejectedResidents',
            'occupiedHouseholds', 'vacantHouseholds', 'totalBlocks', 'totalLots',
            'coverage', 'lastHouseholdAdded', 'lastResidentAdded', 'recentActivity'
        );
    }

    public function getVillageStatisticsApi(int $villageId): array
    {
        $stats = $this->getVillageStatistics($villageId);
        $village = Village::withCount([
            'households as total_households',
            'residents as total_residents' => function ($q) {
                $q->where('status', 'active');
            },
        ])->find($villageId);

        $president = $village->officers()->where('position', 'like', '%President%')->where('status', 'active')->first();
        $secretary = $village->officers()->where('position', 'like', '%Secretary%')->where('status', 'active')->first();

        $totalMembers = Household::where('village_id', $villageId)
            ->withCount(['residents as member_count' => function ($q) {
                $q->where('status', 'active');
            }])
            ->get()
            ->sum('member_count');

        $totalBlocks = Household::where('village_id', $villageId)
            ->distinct('block_number')->whereNotNull('block_number')
            ->count('block_number');
        $totalLots = Household::where('village_id', $villageId)
            ->distinct('lot_number')->whereNotNull('lot_number')
            ->count('lot_number');

        $coverage = $stats['total'] > 0 ? round(($stats['mapped'] / $stats['total']) * 100, 1) : 0;

        $lastHouseholdAdded = Household::where('village_id', $villageId)->latest('created_at')->value('created_at');
        $lastResidentAdded = Resident::where('village_id', $villageId)->latest('created_at')->value('created_at');

        return [
            'general' => [
                'total_households' => $village->total_households ?? $stats['total'],
                'total_residents' => $village->total_residents ?? $stats['totalResidents'],
                'total_members' => $totalMembers,
            ],
            'gis' => [
                'mapped' => $stats['mapped'],
                'unmapped' => $stats['unmapped'],
                'coverage' => $coverage,
                'has_gis_location' => $stats['hasGisLocation'],
            ],
            'approval' => [
                'approved_households' => $stats['byApproval']['approved'] ?? 0,
                'pending_households' => $stats['byApproval']['pending'] ?? 0,
                'rejected_households' => $stats['byApproval']['rejected'] ?? 0,
                'approved_residents' => Resident::where('village_id', $villageId)->where('approval_status', 'approved')->count(),
                'pending_residents' => Resident::where('village_id', $villageId)->where('approval_status', 'pending')->count(),
                'rejected_residents' => Resident::where('village_id', $villageId)->where('approval_status', 'rejected')->count(),
            ],
            'household' => [
                'occupied' => $stats['byStatus']['active'] ?? 0,
                'vacant' => $stats['byStatus']['inactive'] ?? 0,
                'total_blocks' => $totalBlocks,
                'total_lots' => $totalLots,
            ],
            'officers' => [
                'president' => $president?->full_name ?? 'N/A',
                'secretary' => $secretary?->full_name ?? 'N/A',
            ],
            'last_updated' => [
                'last_gis_update' => $village->updated_at?->format('M d, Y h:i A') ?? 'N/A',
                'last_household_added' => $lastHouseholdAdded ? \Carbon\Carbon::parse($lastHouseholdAdded)->format('M d, Y h:i A') : 'N/A',
                'last_resident_added' => $lastResidentAdded ? \Carbon\Carbon::parse($lastResidentAdded)->format('M d, Y h:i A') : 'N/A',
            ],
        ];
    }

    public function getSystemStatisticsApi(): array
    {
        $stats = $this->getSystemStatistics();
        $villages = $this->getVillagesWithCoords();

        return [
            'general' => [
                'total_villages' => $stats['totalVillages'],
                'total_households' => $stats['totalHouseholds'],
                'total_residents' => $stats['totalResidents'],
            ],
            'gis' => [
                'mapped' => $stats['totalMapped'],
                'unmapped' => $stats['totalUnmapped'],
                'coverage' => $stats['coverage'],
            ],
            'approval' => [
                'approved_households' => $stats['approvedHouseholds'],
                'pending_households' => $stats['pendingHouseholds'],
                'rejected_households' => $stats['rejectedHouseholds'],
                'approved_residents' => $stats['approvedResidents'],
                'pending_residents' => $stats['pendingResidents'],
                'rejected_residents' => $stats['rejectedResidents'],
            ],
            'household' => [
                'occupied' => $stats['occupiedHouseholds'],
                'vacant' => $stats['vacantHouseholds'],
                'total_blocks' => $stats['totalBlocks'],
                'total_lots' => $stats['totalLots'],
            ],
            'recent_activity' => $stats['recentActivity'],
            'last_updated' => [
                'last_household_added' => $stats['lastHouseholdAdded'] ? \Carbon\Carbon::parse($stats['lastHouseholdAdded'])->format('M d, Y h:i A') : 'N/A',
                'last_resident_added' => $stats['lastResidentAdded'] ? \Carbon\Carbon::parse($stats['lastResidentAdded'])->format('M d, Y h:i A') : 'N/A',
            ],
        ];
    }

    public function getVillageCenter(int $villageId): ?array
    {
        $village = Village::find($villageId);

        if (! $village) {
            return null;
        }

        if ($village->latitude && $village->longitude) {
            return [
                'lat' => (float) $village->latitude,
                'lng' => (float) $village->longitude,
                'name' => $village->village_name,
            ];
        }

        $center = Household::where('village_id', $villageId)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->selectRaw('AVG(latitude) as lat, AVG(longitude) as lng')
            ->first();

        return [
            'lat' => $center->lat ? (float) $center->lat : 9.2372,
            'lng' => $center->lng ? (float) $center->lng : 125.5415,
            'name' => $village->village_name,
        ];
    }

    public function getSystemCenter(): array
    {
        $villageWithCoords = Village::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->first();

        if ($villageWithCoords) {
            return [
                'lat' => (float) $villageWithCoords->latitude,
                'lng' => (float) $villageWithCoords->longitude,
            ];
        }

        $center = Household::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->selectRaw('AVG(latitude) as lat, AVG(longitude) as lng')
            ->first();

        if ($center->lat && $center->lng) {
            return ['lat' => (float) $center->lat, 'lng' => (float) $center->lng];
        }

        return ['lat' => 9.2372, 'lng' => 125.5415];
    }

    public function getExportData(?int $villageId = null)
    {
        $builder = Household::where('approval_status', 'approved')
            ->with('village:id,village_name,village_code');

        if ($villageId) {
            $builder->where('village_id', $villageId);
        }

        return $builder->select([
            'household_number', 'household_head_name', 'latitude', 'longitude',
            'block_number', 'lot_number', 'address', 'village_id',
            'household_type', 'house_ownership', 'house_status', 'status', 'purok', 'contact_number',
        ])
            ->orderBy('village_id')
            ->orderBy('household_number')
            ->get();
    }
}
