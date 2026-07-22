<?php

namespace App\Http\Controllers;

use App\Models\Village;
use App\Services\Analytics\EducationAnalyticsService;
use Illuminate\View\View;

class EducationAnalyticsController extends Controller
{
    use \App\Http\Traits\VillageAccessAware;

    public function __construct(
        protected EducationAnalyticsService $education
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $villageId = $this->getVillageId($user);

        $summary = $this->education->getSummaryStats($villageId);
        $attainmentDist = $this->education->getAttainmentDistribution($villageId);
        $educationLevelDist = $this->education->getEducationLevelDistribution($villageId);
        $schoolDist = $this->education->getSchoolNameDistribution($villageId);
        $scholarshipByAttainment = $this->education->getScholarshipByAttainment($villageId);
        $insights = $this->education->getInsights($villageId);

        $villages = Village::where('status', 'active')->get();
        $villageComparison = [];

        if (!$villageId) {
            $villageComparison = $this->education->getVillageComparison();
        }

        return view('analytics.education.index', compact(
            'summary', 'attainmentDist', 'educationLevelDist',
            'schoolDist', 'scholarshipByAttainment', 'insights',
            'villages', 'villageComparison', 'villageId'
        ));
    }

    public function village(int $villageId): View
    {
        $this->enforceVillageAccess(auth()->user(), $villageId);

        $village = Village::findOrFail($villageId);
        $summary = $this->education->getSummaryStats($villageId);
        $attainmentDist = $this->education->getAttainmentDistribution($villageId);
        $educationLevelDist = $this->education->getEducationLevelDistribution($villageId);
        $schoolDist = $this->education->getSchoolNameDistribution($villageId);
        $scholarshipByAttainment = $this->education->getScholarshipByAttainment($villageId);
        $insights = $this->education->getInsights($villageId);

        return view('analytics.education.village', compact(
            'village', 'summary', 'attainmentDist', 'educationLevelDist',
            'schoolDist', 'scholarshipByAttainment', 'insights'
        ));
    }

}
