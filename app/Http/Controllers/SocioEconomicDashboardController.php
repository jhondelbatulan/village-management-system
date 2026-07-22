<?php

namespace App\Http\Controllers;

use App\Models\Village;
use App\Services\Analytics\SocioEconomicAnalyticsService;
use App\Services\Analytics\OccupationAnalyticsService;
use App\Services\Analytics\SkillsAnalyticsService;
use App\Services\Analytics\LivelihoodAnalyticsService;
use App\Services\Analytics\CertificationAnalyticsService;
use App\Services\Analytics\CommunityParticipationAnalyticsService;
use App\Services\Analytics\PopulationPyramidService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SocioEconomicDashboardController extends Controller
{
    use \App\Http\Traits\VillageAccessAware;

    public function __construct(
        protected SocioEconomicAnalyticsService $socioEconomic,
        protected OccupationAnalyticsService $occupation,
        protected SkillsAnalyticsService $skills,
        protected LivelihoodAnalyticsService $livelihood,
        protected CertificationAnalyticsService $certification,
        protected CommunityParticipationAnalyticsService $community,
        protected PopulationPyramidService $pyramid
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $villageId = $this->getVillageId($user);

        $summary = $this->socioEconomic->getExecutiveSummary($villageId);
        $incomeAnalysis = $this->socioEconomic->getIncomeAnalysis($villageId);
        $govtStats = $this->socioEconomic->getGovernmentAssistanceStats($villageId);
        $specialPop = $this->socioEconomic->getSpecialPopulationStats($villageId);
        $insights = $this->socioEconomic->getSocioEconomicInsights($villageId);

        $pyramidData = $this->pyramid->getPyramidData($villageId);
        $pyramidStats = $this->pyramid->getSummaryStats($villageId);
        $pyramidAgeDist = $this->pyramid->getAgeGroupDistribution($villageId);

        $occDistribution = $this->occupation->getOccupationDistribution($villageId);
        $topOccupations = $this->occupation->getTopOccupations($villageId, 10);
        $occByGender = $this->occupation->getOccupationByGender($villageId);

        $skillDist = $this->skills->getCombinedSkillDistribution($villageId);
        $skillInventory = $this->socioEconomic->getExecutiveSummary($villageId);

        $livelihoodDist = $this->livelihood->getLivelihoodDistribution($villageId);
        $incomeRange = $this->livelihood->getIncomeRangeDistribution($villageId);
        $incomeStats = $this->livelihood->getIncomeStats($villageId);
        $incomeByGender = $this->livelihood->getIncomeByGender($villageId);

        $certDist = $this->certification->getCertificationDistribution($villageId);
        $topCerts = $this->certification->getTopCertifications($villageId, 10);

        $commDist = $this->community->getActivityDistribution($villageId);
        $topActivities = $this->community->getTopActivities($villageId, 10);

        $villages = Village::where('status', 'active')->get();
        $villageComparison = !$villageId ? $this->socioEconomic->getVillageComparison() : [];

        return view('analytics.socio-economic.index', compact(
            'summary', 'incomeAnalysis', 'govtStats', 'specialPop', 'insights',
            'pyramidData', 'pyramidStats', 'pyramidAgeDist',
            'occDistribution', 'topOccupations', 'occByGender',
            'skillDist', 'livelihoodDist', 'incomeRange', 'incomeStats', 'incomeByGender',
            'certDist', 'topCerts', 'commDist', 'topActivities',
            'villages', 'villageComparison', 'villageId'
        ));
    }

    public function village(int $villageId): View
    {
        $this->enforceVillageAccess(auth()->user(), $villageId);

        $village = Village::findOrFail($villageId);

        $summary = $this->socioEconomic->getExecutiveSummary($villageId);
        $incomeAnalysis = $this->socioEconomic->getIncomeAnalysis($villageId);
        $govtStats = $this->socioEconomic->getGovernmentAssistanceStats($villageId);
        $specialPop = $this->socioEconomic->getSpecialPopulationStats($villageId);
        $insights = $this->socioEconomic->getSocioEconomicInsights($villageId);

        $pyramidData = $this->pyramid->getPyramidData($villageId);
        $pyramidStats = $this->pyramid->getSummaryStats($villageId);
        $pyramidAgeDist = $this->pyramid->getAgeGroupDistribution($villageId);

        $occDistribution = $this->occupation->getOccupationDistribution($villageId);
        $topOccupations = $this->occupation->getTopOccupations($villageId, 10);
        $occByGender = $this->occupation->getOccupationByGender($villageId);

        $skillDist = $this->skills->getCombinedSkillDistribution($villageId);

        $livelihoodDist = $this->livelihood->getLivelihoodDistribution($villageId);
        $incomeRange = $this->livelihood->getIncomeRangeDistribution($villageId);
        $incomeStats = $this->livelihood->getIncomeStats($villageId);
        $incomeByGender = $this->livelihood->getIncomeByGender($villageId);

        $certDist = $this->certification->getCertificationDistribution($villageId);
        $topCerts = $this->certification->getTopCertifications($villageId, 10);

        $commDist = $this->community->getActivityDistribution($villageId);
        $topActivities = $this->community->getTopActivities($villageId, 10);

        return view('analytics.socio-economic.village', compact(
            'village', 'summary', 'incomeAnalysis', 'govtStats', 'specialPop', 'insights',
            'pyramidData', 'pyramidStats', 'pyramidAgeDist',
            'occDistribution', 'topOccupations', 'occByGender',
            'skillDist', 'livelihoodDist', 'incomeRange', 'incomeStats', 'incomeByGender',
            'certDist', 'topCerts', 'commDist', 'topActivities'
        ));
    }

    public function export(Request $request, string $format)
    {
        $user = auth()->user();
        $villageId = $this->getVillageId($user);
        $summary = $this->socioEconomic->getExecutiveSummary($villageId);
        $govtStats = $this->socioEconomic->getGovernmentAssistanceStats($villageId);
        $specialPop = $this->socioEconomic->getSpecialPopulationStats($villageId);
        $pyramidStats = $this->pyramid->getSummaryStats($villageId);
        $incomeStats = $this->livelihood->getIncomeStats($villageId);

        if ($format === 'csv') {
            $filename = 'socio_economic_analytics_' . date('Y-m-d') . '.csv';
            $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"{$filename}\""];
            $callback = function () use ($summary, $govtStats, $specialPop, $pyramidStats, $incomeStats) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Socio-Economic Analytics Report', date('F d, Y')]);
                fputcsv($handle, []);
                fputcsv($handle, ['--- Executive Summary ---']);
                fputcsv($handle, ['Total Residents', $summary['total_residents']]);
                fputcsv($handle, ['Avg Age', $summary['average_age'], 'Median Age', $summary['median_age']]);
                fputcsv($handle, ['Occupation Coverage', $summary['occupation_coverage'] . '%', 'Skill Coverage', $summary['skill_coverage'] . '%']);
                fputcsv($handle, ['Participation Rate', $summary['participation_rate'] . '%']);
                fputcsv($handle, []);
                fputcsv($handle, ['--- Government Programs ---']);
                foreach ($govtStats['memberships'] as $k => $v) { fputcsv($handle, [$k, $v]); }
                foreach ($govtStats['assistance'] as $k => $v) { fputcsv($handle, [$k, $v]); }
                fputcsv($handle, []);
                fputcsv($handle, ['--- Special Population ---']);
                foreach ($specialPop as $k => $v) { if ($k !== 'total') fputcsv($handle, [$k, $v]); }
                fclose($handle);
            };
            return response()->stream($callback, 200, $headers);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('analytics.exports.socio-economic-pdf', compact('summary', 'govtStats', 'specialPop', 'pyramidStats', 'incomeStats'));
        return $pdf->download('socio_economic_analytics_' . date('Y-m-d') . '.pdf');

        return back()->with('error', 'Unsupported export format.');
    }

}
