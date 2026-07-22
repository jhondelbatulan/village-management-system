<?php

namespace App\Http\Controllers;

use App\Models\Village;
use App\Services\Analytics\DependencyRatioService;
use App\Services\Analytics\PopulationDensityService;
use App\Services\Analytics\GrowthAnalyticsService;
use App\Services\Analytics\ResidencyDurationAnalyticsService;
use App\Services\Analytics\MigrationAnalyticsService;
use App\Services\Analytics\HouseholdStabilityService;
use App\Services\Analytics\CommunityStructureService;
use App\Services\Analytics\PlanningIndicatorService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdvancedDemographicsController extends Controller
{
    use \App\Http\Traits\VillageAccessAware;

    public function __construct(
        protected DependencyRatioService $dependency,
        protected PopulationDensityService $density,
        protected GrowthAnalyticsService $growth,
        protected ResidencyDurationAnalyticsService $residency,
        protected MigrationAnalyticsService $migration,
        protected HouseholdStabilityService $householdStability,
        protected CommunityStructureService $communityStructure,
        protected PlanningIndicatorService $planning
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $villageId = $this->getVillageId($user);

        $villages = Village::where('status', 'active')->get();
        $dependencySummary = $this->dependency->getSummaryStats($villageId);
        $dependencyTrend = $this->dependency->getTrend($villageId, 12);
        $ageDistribution = $this->dependency->getAgeGroupDistribution($villageId);
        $dependencyInsights = $this->dependency->getInsights($villageId);
        $densitySummary = $this->density->getSummaryStats($villageId);
        $densityRankings = $this->density->getVillageRankings();
        $densityInsights = $this->density->getInsights($villageId);
        $growthSummary = $this->growth->getSummaryStats($villageId);
        $growthTrend = $this->growth->getMonthlyTrend($villageId, 12);
        $quarterlyTrend = $this->growth->getQuarterlyTrend($villageId);
        $growthInsights = $this->growth->getInsights($villageId);
        $residencySummary = $this->residency->getSummaryStats($villageId);
        $residencyInsights = $this->residency->getInsights($villageId);
        $migrationSummary = $this->migration->getSummaryStats($villageId);
        $migrationTrend = $this->migration->getMonthlyTrend($villageId, 12);
        $migrationInsights = $this->migration->getInsights($villageId);
        $householdSummary = $this->householdStability->getSummaryStats($villageId);
        $householdInsights = $this->householdStability->getInsights($villageId);
        $communitySummary = $this->communityStructure->getSummaryStats($villageId);
        $radarData = $this->communityStructure->getRadarData($villageId);
        $communityInsights = $this->communityStructure->getInsights($villageId);
        $villageComparison = !$villageId ? $this->planning->getDemographicComparison() : [];
        $rankings = !$villageId ? $this->planning->getDemographicRankings() : [];
        $execInsights = $this->planning->getExecutiveInsights($villageId);

        return view('analytics.advanced-demographics.index', compact(
            'villages', 'villageId',
            'dependencySummary', 'dependencyTrend', 'ageDistribution', 'dependencyInsights',
            'densitySummary', 'densityRankings', 'densityInsights',
            'growthSummary', 'growthTrend', 'quarterlyTrend', 'growthInsights',
            'residencySummary', 'residencyInsights',
            'migrationSummary', 'migrationTrend', 'migrationInsights',
            'householdSummary', 'householdInsights',
            'communitySummary', 'radarData', 'communityInsights',
            'villageComparison', 'rankings', 'execInsights'
        ));
    }

    public function village(int $villageId): View
    {
        $user = auth()->user();
        $this->enforceVillageAccess($user, $villageId);

        $village = Village::findOrFail($villageId);
        $dependencySummary = $this->dependency->getSummaryStats($villageId);
        $dependencyTrend = $this->dependency->getTrend($villageId, 12);
        $ageDistribution = $this->dependency->getAgeGroupDistribution($villageId);
        $dependencyInsights = $this->dependency->getInsights($villageId);
        $densitySummary = $this->density->getSummaryStats($villageId);
        $densityInsights = $this->density->getInsights($villageId);
        $growthSummary = $this->growth->getSummaryStats($villageId);
        $growthTrend = $this->growth->getMonthlyTrend($villageId, 12);
        $quarterlyTrend = $this->growth->getQuarterlyTrend($villageId);
        $growthInsights = $this->growth->getInsights($villageId);
        $residencySummary = $this->residency->getSummaryStats($villageId);
        $residencyBrackets = $this->residency->getDurationBrackets($villageId);
        $residencyInsights = $this->residency->getInsights($villageId);
        $migrationSummary = $this->migration->getSummaryStats($villageId);
        $migrationTrend = $this->migration->getMonthlyTrend($villageId, 12);
        $migrationInsights = $this->migration->getInsights($villageId);
        $householdSummary = $this->householdStability->getSummaryStats($villageId);
        $householdInsights = $this->householdStability->getInsights($villageId);
        $communitySummary = $this->communityStructure->getSummaryStats($villageId);
        $radarData = $this->communityStructure->getRadarData($villageId);
        $communityInsights = $this->communityStructure->getInsights($villageId);
        $execInsights = $this->planning->getExecutiveInsights($villageId);

        return view('analytics.advanced-demographics.village', compact(
            'village',
            'dependencySummary', 'dependencyTrend', 'ageDistribution', 'dependencyInsights',
            'densitySummary', 'densityInsights',
            'growthSummary', 'growthTrend', 'quarterlyTrend', 'growthInsights',
            'residencySummary', 'residencyBrackets', 'residencyInsights',
            'migrationSummary', 'migrationTrend', 'migrationInsights',
            'householdSummary', 'householdInsights',
            'communitySummary', 'radarData', 'communityInsights',
            'execInsights'
        ));
    }

    public function export(Request $request, string $format)
    {
        $user = auth()->user();
        $villageId = $this->getVillageId($user);
        $indicators = $this->planning->getAllIndicators($villageId);
        $comparison = $this->planning->getDemographicComparison();
        $insights = $this->planning->getExecutiveInsights($villageId);

        if ($format === 'csv') {
            $filename = 'advanced_demographics_' . date('Y-m-d') . '.csv';
            $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"{$filename}\""];
            $callback = function () use ($indicators, $comparison, $insights) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Advanced Demographics & Planning Analytics Report', date('F d, Y')]);
                fputcsv($handle, []);
                fputcsv($handle, ['Indicator', 'Value']);
                fputcsv($handle, ['Dependency Ratio', $indicators['dependency']['total_dependency_ratio'] . '%']);
                fputcsv($handle, ['Child Dependency Ratio', $indicators['dependency']['child_dependency_ratio'] . '%']);
                fputcsv($handle, ['Senior Dependency Ratio', $indicators['dependency']['senior_dependency_ratio'] . '%']);
                fputcsv($handle, ['Population Density', $indicators['density']['population_density'] . ' people/km²']);
                fputcsv($handle, ['Household Density', $indicators['density']['household_density'] . ' HH/km²']);
                fputcsv($handle, ['Annual Growth', $indicators['growth']['annual_growth'] . '%']);
                fputcsv($handle, ['Average Residency', $indicators['residency']['average_years'] . ' years']);
                fputcsv($handle, ['Migration Rate', $indicators['migration']['migration_rate'] . '%']);
                fputcsv($handle, ['Stability Index', $indicators['household_stability']['stability_index']]);
                fputcsv($handle, []);
                if (!empty($comparison)) {
                    fputcsv($handle, ['Village Comparison']);
                    fputcsv($handle, ['Village', 'Population', 'Density', 'Growth', 'Avg Age', 'Dependency', 'HH Density', 'Residency']);
                    foreach ($comparison as $c) {
                        fputcsv($handle, [
                            $c['village_name'], $c['population'], $c['population_density'],
                            $c['annual_growth'] . '%', $c['average_age'], $c['dependency_ratio'] . '%',
                            $c['household_density'], $c['avg_residency_years'] . 'y'
                        ]);
                    }
                }
                fclose($handle);
            };
            return response()->stream($callback, 200, $headers);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('analytics.exports.advanced-demographics-pdf', compact('indicators', 'comparison', 'insights'));
        return $pdf->download('advanced_demographics_' . date('Y-m-d') . '.pdf');

        return back()->with('error', 'Unsupported export format.');
    }

}
