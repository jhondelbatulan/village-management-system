<?php

namespace App\Http\Controllers;

use App\Models\Village;
use App\Services\Analytics\DemographicAnalyticsService;
use App\Services\Analytics\PopulationAnalyticsService;
use App\Services\Analytics\HouseholdAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DemographicDashboardController extends Controller
{
    use \App\Http\Traits\VillageAccessAware;

    public function __construct(
        protected DemographicAnalyticsService $demographic,
        protected PopulationAnalyticsService $population,
        protected HouseholdAnalyticsService $household
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $villageId = $this->getVillageId($user);

        $summary = $this->demographic->getDemographicSummary($villageId);
        $genderDist = $this->population->getGenderDistribution($villageId);
        $fineAgeDist = $this->demographic->getFineAgeDistribution($villageId);
        $ageDist = $this->population->getAgeDistribution($villageId);
        $civilDist = $this->population->getCivilStatusDistribution($villageId);
        $specialPop = $this->demographic->getSpecialPopulation($villageId);
        $nationalityDist = $this->demographic->getNationalityDistribution($villageId);
        $residencyDist = $this->demographic->getResidencyAnalytics($villageId);
        $householdDist = $this->population->getHouseholdDistribution($villageId);
        $compositionDist = $this->household->getCompositionDistribution($villageId);
        $insights = $this->demographic->getDemographicInsights($villageId);

        $villages = Village::where('status', 'active')->get();
        $villageComparison = [];
        if (!$villageId) {
            $villageComparison = $this->demographic->getVillageComparison();
        }

        return view('analytics.demographic.index', compact(
            'summary', 'genderDist', 'fineAgeDist', 'ageDist', 'civilDist',
            'specialPop', 'nationalityDist', 'residencyDist', 'householdDist',
            'compositionDist', 'insights', 'villages', 'villageComparison', 'villageId'
        ));
    }

    public function village(int $villageId): View
    {
        $this->enforceVillageAccess(auth()->user(), $villageId);

        $village = Village::findOrFail($villageId);

        $summary = $this->demographic->getDemographicSummary($villageId);
        $genderDist = $this->population->getGenderDistribution($villageId);
        $fineAgeDist = $this->demographic->getFineAgeDistribution($villageId);
        $ageDist = $this->population->getAgeDistribution($villageId);
        $civilDist = $this->population->getCivilStatusDistribution($villageId);
        $specialPop = $this->demographic->getSpecialPopulation($villageId);
        $nationalityDist = $this->demographic->getNationalityDistribution($villageId);
        $residencyDist = $this->demographic->getResidencyAnalytics($villageId);
        $householdDist = $this->population->getHouseholdDistribution($villageId);
        $compositionDist = $this->household->getCompositionDistribution($villageId);
        $insights = $this->demographic->getDemographicInsights($villageId);

        return view('analytics.demographic.village', compact(
            'village', 'summary', 'genderDist', 'fineAgeDist', 'ageDist',
            'civilDist', 'specialPop', 'nationalityDist', 'residencyDist',
            'householdDist', 'compositionDist', 'insights'
        ));
    }

}
