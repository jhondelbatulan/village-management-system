<?php

namespace App\Http\Controllers;

use App\Models\Village;
use App\Services\Analytics\PlanningIndicatorService;
use App\Services\Analytics\DependencyRatioService;
use App\Services\Analytics\PopulationDensityService;
use App\Services\Analytics\GrowthAnalyticsService;
use App\Services\Analytics\ResidencyDurationAnalyticsService;
use App\Services\Analytics\MigrationAnalyticsService;
use App\Services\Analytics\HouseholdStabilityService;
use App\Services\Analytics\CommunityStructureService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanningAnalyticsController extends Controller
{
    use \App\Http\Traits\VillageAccessAware;

    public function __construct(
        protected PlanningIndicatorService $planning,
        protected DependencyRatioService $dependency,
        protected PopulationDensityService $density,
        protected GrowthAnalyticsService $growth,
        protected ResidencyDurationAnalyticsService $residency,
        protected MigrationAnalyticsService $migration,
        protected HouseholdStabilityService $householdStability,
        protected CommunityStructureService $communityStructure
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $villageId = $this->getVillageId($user);

        $villages = Village::where('status', 'active')->get();
        $indicators = $this->planning->getAllIndicators($villageId);
        $comparison = $this->planning->getDemographicComparison();
        $rankings = $this->planning->getDemographicRankings();
        $execInsights = $this->planning->getExecutiveInsights($villageId);

        $dependencyComparison = !$villageId ? $this->dependency->getVillageComparison() : [];
        $densityComparison = !$villageId ? $this->density->getVillageComparison() : [];
        $growthComparison = !$villageId ? $this->growth->getVillageComparison() : [];
        $householdComparison = !$villageId ? $this->householdStability->getVillageComparison() : [];
        $communityComparison = !$villageId ? $this->communityStructure->getVillageComparison() : [];

        return view('analytics.planning.index', compact(
            'villages', 'villageId', 'indicators', 'comparison', 'rankings', 'execInsights',
            'dependencyComparison', 'densityComparison', 'growthComparison',
            'householdComparison', 'communityComparison'
        ));
    }

    public function village(int $villageId): View
    {
        $this->enforceVillageAccess(auth()->user(), $villageId);

        $village = Village::findOrFail($villageId);
        $indicators = $this->planning->getAllIndicators($villageId);
        $execInsights = $this->planning->getExecutiveInsights($villageId);
        $communityStructure = $this->communityStructure->getStructureBreakdown($villageId);
        $radarData = $this->communityStructure->getRadarData($villageId);

        return view('analytics.planning.village', compact(
            'village', 'indicators', 'execInsights', 'communityStructure', 'radarData'
        ));
    }

    public function search(Request $request): View
    {
        $query = $request->input('q', '');
        $villageId = $request->input('village_id');
        if ($villageId) {
            $this->enforceVillageAccess(auth()->user(), (int) $villageId);
            $villageId = (int) $villageId;
        } else {
            $villageId = $this->getVillageId(auth()->user());
        }
        $results = $this->planning->search($query, $villageId);
        $villages = Village::where('status', 'active')->get();

        return view('analytics.planning.search', compact('query', 'results', 'villages', 'villageId'));
    }

    public function filter(Request $request): View
    {
        $filters = $request->only(['village_id', 'year', 'quarter', 'month', 'age_group']);
        $villageId = $filters['village_id'] ?? null;
        if ($villageId) {
            $this->enforceVillageAccess(auth()->user(), (int) $villageId);
            $villageId = (int) $villageId;
        } else {
            $villageId = $this->getVillageId(auth()->user());
        }
        $results = $this->planning->filter($filters, $villageId);
        $villages = Village::where('status', 'active')->get();

        return view('analytics.planning.filter-results', compact('filters', 'results', 'villages'));
    }

    public function export(Request $request, string $format)
    {
        $user = auth()->user();
        $villageId = $this->getVillageId($user);
        $indicators = $this->planning->getAllIndicators($villageId);
        $comparison = $this->planning->getDemographicComparison();
        $insights = $this->planning->getExecutiveInsights($villageId);

        if ($format === 'csv') {
            $filename = 'planning_analytics_' . date('Y-m-d') . '.csv';
            $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"{$filename}\""];
            $callback = function () use ($indicators, $comparison, $insights) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Planning Analytics Report', date('F d, Y')]);
                fputcsv($handle, []);
                fputcsv($handle, ['Planning Indicators']);
                fputcsv($handle, ['Indicator', 'Value']);
                foreach ($indicators as $section => $data) {
                    foreach ($data as $key => $value) {
                        if (is_numeric($value) || is_string($value)) {
                            fputcsv($handle, [ucfirst(str_replace('_', ' ', $key)), $value]);
                        }
                    }
                    fputcsv($handle, []);
                }
                fclose($handle);
            };
            return response()->stream($callback, 200, $headers);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('analytics.exports.planning-pdf', compact('indicators', 'comparison', 'insights'));
        return $pdf->download('planning_analytics_' . date('Y-m-d') . '.pdf');

        return back()->with('error', 'Unsupported export format.');
    }

}
