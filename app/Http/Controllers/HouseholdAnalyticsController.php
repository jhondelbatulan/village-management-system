<?php

namespace App\Http\Controllers;

use App\Models\Village;
use App\Services\Analytics\HouseholdAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HouseholdAnalyticsController extends Controller
{
    use \App\Http\Traits\VillageAccessAware;

    public function __construct(
        protected HouseholdAnalyticsService $household
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $villageId = $this->getVillageId($user);

        $summary = $this->household->getHouseholdSummary($villageId);
        $statusDist = $this->household->getStatusDistribution($villageId);
        $sizeDist = $this->household->getSizeDistribution($villageId);
        $compositionDist = $this->household->getCompositionDistribution($villageId);
        $typeDist = $this->household->getTypeDistribution($villageId);
        $blockDist = $this->household->getBlockDistribution($villageId);
        $blockLotStats = $this->household->getBlockLotStats($villageId);
        $occupancyStats = $this->household->getOccupancyStats($villageId);
        $monthlyTrend = $this->household->getMonthlyTrend($villageId, 12);
        $yearlyTrend = $this->household->getYearlyTrend($villageId, 5);
        $growth = $this->household->getGrowthAnalysis($villageId);
        $regStats = $this->household->getRegistrationStats($villageId);
        $insights = $this->household->getHouseholdInsights($villageId);

        $villages = Village::where('status', 'active')->get();
        $villageComparison = [];
        if (!$villageId) {
            $villageComparison = $this->household->getVillageComparison();
        }

        return view('analytics.household.index', compact(
            'summary', 'statusDist', 'sizeDist', 'compositionDist', 'typeDist',
            'blockDist', 'blockLotStats', 'occupancyStats', 'monthlyTrend',
            'yearlyTrend', 'growth', 'regStats', 'insights', 'villages',
            'villageComparison', 'villageId'
        ));
    }

    public function village(int $villageId): View
    {
        $this->enforceVillageAccess(auth()->user(), $villageId);

        $village = Village::findOrFail($villageId);

        $summary = $this->household->getHouseholdSummary($villageId);
        $statusDist = $this->household->getStatusDistribution($villageId);
        $sizeDist = $this->household->getSizeDistribution($villageId);
        $compositionDist = $this->household->getCompositionDistribution($villageId);
        $typeDist = $this->household->getTypeDistribution($villageId);
        $blockDist = $this->household->getBlockDistribution($villageId);
        $blockLotStats = $this->household->getBlockLotStats($villageId);
        $occupancyStats = $this->household->getOccupancyStats($villageId);
        $monthlyTrend = $this->household->getMonthlyTrend($villageId, 12);
        $yearlyTrend = $this->household->getYearlyTrend($villageId, 5);
        $growth = $this->household->getGrowthAnalysis($villageId);
        $regStats = $this->household->getRegistrationStats($villageId);
        $insights = $this->household->getHouseholdInsights($villageId);

        return view('analytics.household.village', compact(
            'village', 'summary', 'statusDist', 'sizeDist', 'compositionDist',
            'typeDist', 'blockDist', 'blockLotStats', 'occupancyStats',
            'monthlyTrend', 'yearlyTrend', 'growth', 'regStats', 'insights'
        ));
    }

}
