<?php

namespace App\Http\Controllers;

use App\Models\Village;
use App\Services\Analytics\OccupationAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OccupationAnalyticsController extends Controller
{
    use \App\Http\Traits\VillageAccessAware;

    public function __construct(
        protected OccupationAnalyticsService $occupation
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $villageId = $this->getVillageId($user);

        $summary = $this->occupation->getSummaryStats($villageId);
        $distribution = $this->occupation->getOccupationDistribution($villageId);
        $topOccupations = $this->occupation->getTopOccupations($villageId);
        $leastCommon = $this->occupation->getLeastCommonOccupations($villageId);
        $byGender = $this->occupation->getOccupationByGender($villageId);
        $rankings = $this->occupation->getOccupationRankings();
        $trend = $this->occupation->getOccupationTrend($villageId);
        $insights = $this->occupation->getInsights($villageId);

        $villages = Village::where('status', 'active')->get();
        $villageComparison = !$villageId ? $this->occupation->getVillageComparison() : [];

        return view('analytics.occupation.index', compact(
            'summary', 'distribution', 'topOccupations', 'leastCommon',
            'byGender', 'rankings', 'trend', 'insights',
            'villages', 'villageComparison', 'villageId'
        ));
    }

    public function village(int $villageId): View
    {
        $this->enforceVillageAccess(auth()->user(), $villageId);

        $village = Village::findOrFail($villageId);
        $summary = $this->occupation->getSummaryStats($villageId);
        $distribution = $this->occupation->getOccupationDistribution($villageId);
        $topOccupations = $this->occupation->getTopOccupations($villageId);
        $leastCommon = $this->occupation->getLeastCommonOccupations($villageId);
        $byGender = $this->occupation->getOccupationByGender($villageId);
        $trend = $this->occupation->getOccupationTrend($villageId);
        $rankings = $this->occupation->getOccupationRankings($villageId);
        $insights = $this->occupation->getInsights($villageId);

        return view('analytics.occupation.village', compact(
            'village', 'summary', 'distribution', 'topOccupations',
            'leastCommon', 'byGender', 'rankings', 'trend', 'insights'
        ));
    }

    public function export(Request $request, string $format)
    {
        $user = auth()->user();
        $villageId = $this->getVillageId($user);
        $summary = $this->occupation->getSummaryStats($villageId);
        $rankings = $this->occupation->getOccupationRankings();

        if ($format === 'csv') {
            $filename = 'occupation_analytics_' . date('Y-m-d') . '.csv';
            $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"{$filename}\""];
            $callback = function () use ($rankings, $summary) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Occupation Analytics Report', date('F d, Y')]);
                fputcsv($handle, ['Total Residents', $summary['total'], 'With Occupation', $summary['with_occupation'], 'Coverage', $summary['coverage_rate'] . '%']);
                fputcsv($handle, []);
                fputcsv($handle, ['Rank', 'Occupation', 'Count', 'Percentage']);
                foreach ($rankings as $r) {
                    fputcsv($handle, [$r['rank'], $r['occupation'], $r['count'], $r['percentage'] . '%']);
                }
                fclose($handle);
            };
            return response()->stream($callback, 200, $headers);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('analytics.exports.occupation-pdf', compact('summary', 'rankings'));
        return $pdf->download('occupation_analytics_' . date('Y-m-d') . '.pdf');

        return back()->with('error', 'Unsupported export format.');
    }

}
