<?php

namespace App\Http\Controllers;

use App\Models\Village;
use App\Services\Analytics\HealthcareAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HealthcareController extends Controller
{
    use \App\Http\Traits\VillageAccessAware;
    public function __construct(protected HealthcareAnalyticsService $service) {}

    public function index(): View
    {
        $user = auth()->user();
        $villageId = $this->getVillageId($user);

        $summary = $this->service->getSummaryStats($villageId);
        $coverageDistribution = $this->service->getCoverageDistribution($villageId);
        $villageComparison = !$villageId ? $this->service->getVillageComparison() : [];
        $insights = $this->service->getInsights($villageId);
        $villages = Village::where('status', 'active')->get();

        return view('analytics.healthcare.index', compact(
            'summary', 'villageId', 'villages',
            'coverageDistribution',
            'villageComparison',
            'insights'
        ));
    }

    public function village(int $villageId): View
    {
        $this->enforceVillageAccess(auth()->user(), $villageId);

        $village = Village::findOrFail($villageId);

        $summary = $this->service->getSummaryStats($villageId);
        $coverageDistribution = $this->service->getCoverageDistribution($villageId);
        $insights = $this->service->getInsights($villageId);

        return view('analytics.healthcare.village', compact(
            'village',
            'summary',
            'coverageDistribution',
            'insights'
        ));
    }

    public function export(Request $request, string $format)
    {
        $user = auth()->user();
        $villageId = $this->getVillageId($user);

        $summary = $this->service->getSummaryStats($villageId);
        $coverageDistribution = $this->service->getCoverageDistribution($villageId);
        $insights = $this->service->getInsights($villageId);

        $data = compact('summary', 'coverageDistribution', 'insights');

        if ($format === 'csv') {
            $filename = 'healthcare-report.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            return response()->stream(function () use ($data) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Metric', 'Value']);
                fputcsv($handle, ['Total Covered', $data['summary']->total_covered ?? 0]);
                fputcsv($handle, ['Coverage Rate', $data['summary']->coverage_rate ?? 0]);

                foreach ($data['coverageDistribution'] as $item) {
                    fputcsv($handle, [$item->category, $item->count]);
                }

                fclose($handle);
            }, 200, $headers);
        }

        if ($format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('analytics.exports.healthcare-pdf', $data);
            return $pdf->download('healthcare-report.pdf');
        }

        return back()->with('error', 'Unsupported export format.');
    }

}
