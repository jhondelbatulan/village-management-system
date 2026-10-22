<?php

namespace App\Http\Controllers;

use App\Models\Village;
use App\Services\GisDashboardService;
use App\Services\GisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GisController extends Controller
{
    protected GisService $gis;
    protected GisDashboardService $dashboard;

    public function __construct(GisService $gis, GisDashboardService $dashboard)
    {
        $this->gis = $gis;
        $this->dashboard = $dashboard;
    }

    public function index(): View
    {
        $user = auth()->user();
        $role = $user->role?->role_slug;

        if ($role === 'resident') {
            return $this->residentView($user);
        }

        if (in_array($role, ['admin', 'secretary', 'treasurer']) && $user->village_id) {
            return $this->villageView($user->village_id);
        }

        return $this->superAdminView();
    }

    private function superAdminView(): View
    {
        $villages = $this->gis->getAllVillages();
        $villageMarkers = $this->gis->getVillagesWithCoords();
        $households = $this->gis->getAllHouseholdsWithCoords();
        $center = $this->gis->getSystemCenter();
        $stats = $this->gis->getSystemStatistics();
        $overviewData = $this->dashboard->getSystemOverviewData();

        return view('gis.index', compact('villages', 'villageMarkers', 'households', 'center', 'stats', 'overviewData'));
    }

    private function villageView(int $villageId): View
    {
        $village = Village::findOrFail($villageId);
        $households = $this->gis->getVillageHouseholds($villageId);
        $stats = $this->gis->getVillageStatistics($villageId);
        $center = $this->gis->getVillageCenter($villageId);
        $dashboardData = $this->dashboard->getComprehensiveVillageData($villageId);

        return view('gis.village', compact('village', 'households', 'stats', 'center', 'dashboardData'));
    }

    private function residentView($user): View
    {
        $household = $user->household;
        $villageId = $household?->village_id ?? $user->village_id;
        $village = $villageId ? Village::find($villageId) : null;
        $neighbors = $villageId
            ? $this->gis->getVillageHouseholds($villageId)
            : collect();

        $center = null;
        if ($household && $household->latitude && $household->longitude) {
            $center = [
                'lat' => (float) $household->latitude,
                'lng' => (float) $household->longitude,
            ];
        } elseif ($villageId) {
            $center = $this->gis->getVillageCenter($villageId);
        }

        $hasMapData = ($household && $household->latitude && $household->longitude)
            || $neighbors->isNotEmpty();

        if ($center && $hasMapData) {
            return view('gis.household', [
                'household' => $household?->load('village:id,village_name'),
                'village' => $village,
                'neighbors' => $neighbors,
                'neighborCount' => $neighbors->filter(fn ($h) => ! $household || $h->id !== $household->id)->count(),
                'center' => $center,
            ]);
        }

        return view('gis.no-location');
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:1|max:255']);
        $user = auth()->user();
        $role = $user->role?->role_slug;

        if ($role === 'resident') {
            $household = $user->household;
            if ($household && $household->latitude && $household->longitude) {
                return response()->json([
                    'success' => true,
                    'data' => collect([$household->only([
                        'id', 'household_number', 'household_head_name', 'latitude', 'longitude',
                        'block_number', 'lot_number', 'household_type', 'status', 'approval_status',
                        'village_id', 'address', 'purok',
                    ])]),
                    'count' => 1,
                ]);
            }
            return response()->json(['success' => true, 'data' => collect(), 'count' => 0]);
        }

        $villageId = null;
        if ($role !== 'super_admin') {
            $villageId = $user->village_id;
        }

        $households = $this->gis->searchHouseholds($request->q, $villageId);

        $residents = $this->gis->searchResidents($request->q, $villageId);
        $residentHouseholdIds = $residents->pluck('household_id');
        $residentHouseholds = $households->whereNotIn('id', $residentHouseholdIds);

        $fromResidents = $residents->map(function ($r) {
            $h = $r->household;
            if (! $h) return null;
            return [
                'id' => $h->id,
                'household_number' => $h->household_number,
                'household_head_name' => $h->household_head_name,
                'latitude' => $h->latitude,
                'longitude' => $h->longitude,
                'block_number' => $h->block_number,
                'lot_number' => $h->lot_number,
                'household_type' => $h->household_type,
                'status' => $h->status,
                'approval_status' => $h->approval_status,
                'village_id' => $h->village_id,
                'address' => $h->address,
                'purok' => $h->purok,
                'member_count' => $h->member_count,
            ];
        })->filter()->values();

        $merged = $households->concat($fromResidents)->unique('id')->values();

        return response()->json([
            'success' => true,
            'data' => $merged,
            'count' => $merged->count(),
        ]);
    }

    public function filter(Request $request): JsonResponse
    {
        $user = auth()->user();
        $role = $user->role?->role_slug;

        if ($role === 'resident') {
            $household = $user->household;
            if ($household && $household->latitude && $household->longitude) {
                return response()->json([
                    'success' => true,
                    'data' => collect([$household->only([
                        'id', 'household_number', 'household_head_name', 'latitude', 'longitude',
                        'block_number', 'lot_number', 'household_type', 'status', 'approval_status',
                        'village_id', 'address', 'purok',
                    ])]),
                    'count' => 1,
                ]);
            }
            return response()->json(['success' => true, 'data' => collect(), 'count' => 0]);
        }

        $filters = $request->only([
            'village_id', 'status', 'household_type', 'block_number', 'purok',
            'approval_status', 'mapped', 'unmapped', 'payment_status',
        ]);

        if ($role !== 'super_admin' && $user->village_id) {
            $filters['village_id'] = $user->village_id;
        }

        $households = $this->gis->filterHouseholds($filters);

        return response()->json([
            'success' => true,
            'data' => $households,
            'count' => $households->count(),
        ]);
    }

    public function households(Request $request): View
    {
        $user = auth()->user();
        $role = $user->role?->role_slug;
        $villageId = $request->get('village_id');

        if ($role !== 'super_admin' && $user->village_id) {
            $villageId = $user->village_id;
        }

        $households = $this->gis->getVillageHouseholds($villageId);
        $villages = Village::orderBy('village_name')->get();
        $center = $villageId ? $this->gis->getVillageCenter($villageId) : $this->gis->getSystemCenter();

        return view('gis.households', compact('households', 'villages', 'center', 'villageId'));
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        $role = $user->role?->role_slug;
        $villageId = $request->get('village_id');

        if ($role !== 'super_admin' && $user->village_id) {
            $villageId = $user->village_id;
        }

        $data = $this->gis->getExportData($villageId);

        $csv = "household_number,head_name,latitude,longitude,block,lot,address,village,type,status,approval_status\n";
        foreach ($data as $row) {
            $csv .= implode(',', [
                $row->household_number,
                "\"{$row->household_head_name}\"",
                $row->latitude ?? '',
                $row->longitude ?? '',
                $row->block_number,
                $row->lot_number,
                "\"{$row->address}\"",
                $row->village?->village_code ?? '',
                $row->household_type,
                $row->status,
                $row->approval_status ?? '',
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="household_map_data.csv"',
        ]);
    }

    public function villageApi(int $villageId): JsonResponse
    {
        $user = auth()->user();
        $role = $user->role?->role_slug;

        if ($role !== 'super_admin' && $user->village_id != $villageId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $households = $this->gis->getVillageHouseholds($villageId);
        $stats = $this->gis->getVillageStatistics($villageId);
        $center = $this->gis->getVillageCenter($villageId);

        return response()->json([
            'success' => true,
            'households' => $households,
            'stats' => $stats,
            'center' => $center,
        ]);
    }

    public function statsApi(Request $request): JsonResponse
    {
        $user = auth()->user();
        $role = $user->role?->role_slug;

        if ($role === 'resident') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $villageId = $request->get('village_id');

        if ($role !== 'super_admin' && $user->village_id) {
            $villageId = $user->village_id;
        }

        if ($villageId) {
            $stats = $this->gis->getVillageStatisticsApi((int) $villageId);
        } else {
            $stats = $this->gis->getSystemStatisticsApi();
        }

        return response()->json(['success' => true, 'stats' => $stats]);
    }

    public function villagesApi(): JsonResponse
    {
        $user = auth()->user();
        $role = $user->role?->role_slug;

        if ($role === 'resident') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $villages = $this->gis->getVillagesWithCoords();

        return response()->json(['success' => true, 'villages' => $villages]);
    }

    public function villageAnalyticsApi(int $villageId): JsonResponse
    {
        $village = Village::findOrFail($villageId);

        $popService = app(\App\Services\Analytics\PopulationAnalyticsService::class);
        $hhService = app(\App\Services\Analytics\HouseholdAnalyticsService::class);
        $govService = app(\App\Services\Analytics\GovernmentAssistanceAnalyticsService::class);
        $partService = app(\App\Services\Analytics\ParticipationAnalyticsService::class);

        return response()->json([
            'success' => true,
            'village' => [
                'id' => $village->id,
                'name' => $village->village_name,
                'code' => $village->village_code,
            ],
            'population' => $popService->getVillageStats($villageId),
            'households' => $hhService->getVillageStats($villageId),
            'government' => $govService->getSummaryStats($villageId),
            'participation' => $partService->getSummaryStats($villageId),
        ]);
    }

    public function dashboardDataApi(int $villageId): JsonResponse
    {
        $user = auth()->user();
        $role = $user->role?->role_slug;

        if ($role === 'resident') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($role !== 'super_admin' && $user->village_id != $villageId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $data = $this->dashboard->getComprehensiveVillageData($villageId);

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function systemOverviewApi(): JsonResponse
    {
        $user = auth()->user();
        $role = $user->role?->role_slug;

        if ($role !== 'super_admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $data = $this->dashboard->getSystemOverviewData();

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function latestApi(Request $request): JsonResponse
    {
        $user = auth()->user();
        $role = $user->role?->role_slug;

        if ($role === 'resident') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $villageId = $request->get('village_id');

        if ($role !== 'super_admin') {
            $villageId = $user->village_id;
        }

        $households = $this->gis->getLatestMappedHouseholds(
            $villageId ? (int) $villageId : null,
            max(1, min(50, (int) $request->get('limit', 10)))
        );

        return response()->json(['success' => true, 'data' => $households]);
    }
}
