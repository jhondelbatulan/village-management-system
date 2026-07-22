<?php

namespace App\Http\Controllers;

use App\Models\Village;
use App\Services\Analytics\PopulationAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PopulationAnalyticsController extends Controller
{
    use \App\Http\Traits\VillageAccessAware;

    public function __construct(
        protected PopulationAnalyticsService $population
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $villageId = $this->getVillageId($user);

        $summary = $this->population->getPopulationSummary($villageId);
        $genderDist = $this->population->getGenderDistribution($villageId);
        $ageDist = $this->population->getAgeDistribution($villageId);
        $civilDist = $this->population->getCivilStatusDistribution($villageId);
        $occupationDist = $this->population->getOccupationDistribution($villageId);
        $employmentDist = $this->population->getEmploymentDistribution($villageId);
        $educationDist = $this->population->getEducationDistribution($villageId);
        $householdDist = $this->population->getHouseholdDistribution($villageId);
        $monthlyTrend = $this->population->getMonthlyPopulationTrend($villageId, 12);
        $yearlyTrend = $this->population->getYearlyTrend($villageId, 5);
        $growth = $this->population->getGrowthAnalysis($villageId);
        $insights = $this->population->getPopulationInsights($villageId);
        $averageAge = $this->population->getAverageAge($villageId);

        $villages = Village::where('status', 'active')->get();
        $villageComparison = [];
        $rankings = [];

        if (!$villageId) {
            $villageComparison = $this->population->getVillageComparison();
            $rankings = $this->population->getVillageComparisonRankings();
        }

        return view('analytics.population.index', compact(
            'summary', 'genderDist', 'ageDist', 'civilDist',
            'occupationDist', 'employmentDist', 'educationDist',
            'householdDist', 'monthlyTrend', 'yearlyTrend',
            'growth', 'insights', 'averageAge', 'villages',
            'villageComparison', 'rankings', 'villageId'
        ));
    }

    public function village(int $villageId): View
    {
        $this->enforceVillageAccess(auth()->user(), $villageId);

        $village = Village::findOrFail($villageId);
        $summary = $this->population->getPopulationSummary($villageId);
        $genderDist = $this->population->getGenderDistribution($villageId);
        $ageDist = $this->population->getAgeDistribution($villageId);
        $civilDist = $this->population->getCivilStatusDistribution($villageId);
        $occupationDist = $this->population->getOccupationDistribution($villageId);
        $employmentDist = $this->population->getEmploymentDistribution($villageId);
        $educationDist = $this->population->getEducationDistribution($villageId);
        $householdDist = $this->population->getHouseholdDistribution($villageId);
        $monthlyTrend = $this->population->getMonthlyPopulationTrend($villageId, 12);
        $yearlyTrend = $this->population->getYearlyTrend($villageId, 5);
        $growth = $this->population->getGrowthAnalysis($villageId);
        $insights = $this->population->getPopulationInsights($villageId);
        $averageAge = $this->population->getAverageAge($villageId);

        return view('analytics.population.village', compact(
            'village', 'summary', 'genderDist', 'ageDist', 'civilDist',
            'occupationDist', 'employmentDist', 'educationDist',
            'householdDist', 'monthlyTrend', 'yearlyTrend',
            'growth', 'insights', 'averageAge'
        ));
    }

}
