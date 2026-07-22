<?php

namespace App\Http\Controllers;

use App\Models\Village;
use App\Services\Analytics\EmploymentAnalyticsService;
use Illuminate\View\View;

class EmploymentAnalyticsController extends Controller
{
    use \App\Http\Traits\VillageAccessAware;

    public function __construct(
        protected EmploymentAnalyticsService $employment
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $villageId = $this->getVillageId($user);

        $summary = $this->employment->getSummaryStats($villageId);
        $employmentDist = $this->employment->getEmploymentStatusDistribution($villageId);
        $occupationDist = $this->employment->getOccupationDistribution($villageId);
        $incomeSourceDist = $this->employment->getIncomeSourceDistribution($villageId);
        $incomeRangeDist = $this->employment->getIncomeRangeDistribution($villageId);
        $incomeByGender = $this->employment->getIncomeByGender($villageId);
        $experienceDist = $this->employment->getExperienceDistribution($villageId);
        $jobTitleDist = $this->employment->getJobTitleDistribution($villageId);
        $insights = $this->employment->getInsights($villageId);

        $villages = Village::where('status', 'active')->get();
        $villageComparison = [];

        if (!$villageId) {
            $villageComparison = $this->employment->getVillageComparison();
        }

        return view('analytics.employment.index', compact(
            'summary', 'employmentDist', 'occupationDist',
            'incomeSourceDist', 'incomeRangeDist', 'incomeByGender',
            'experienceDist', 'jobTitleDist', 'insights',
            'villages', 'villageComparison', 'villageId'
        ));
    }

    public function village(int $villageId): View
    {
        $this->enforceVillageAccess(auth()->user(), $villageId);

        $village = Village::findOrFail($villageId);
        $summary = $this->employment->getSummaryStats($villageId);
        $employmentDist = $this->employment->getEmploymentStatusDistribution($villageId);
        $occupationDist = $this->employment->getOccupationDistribution($villageId);
        $incomeSourceDist = $this->employment->getIncomeSourceDistribution($villageId);
        $incomeRangeDist = $this->employment->getIncomeRangeDistribution($villageId);
        $incomeByGender = $this->employment->getIncomeByGender($villageId);
        $experienceDist = $this->employment->getExperienceDistribution($villageId);
        $jobTitleDist = $this->employment->getJobTitleDistribution($villageId);
        $insights = $this->employment->getInsights($villageId);

        return view('analytics.employment.village', compact(
            'village', 'summary', 'employmentDist', 'occupationDist',
            'incomeSourceDist', 'incomeRangeDist', 'incomeByGender',
            'experienceDist', 'jobTitleDist', 'insights'
        ));
    }

}
