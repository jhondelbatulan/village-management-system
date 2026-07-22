<?php

namespace App\Http\Controllers;

use App\Services\Analytics\ExecutiveIntelligenceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExecutiveIntelligenceController extends Controller
{
    use \App\Http\Traits\VillageAccessAware;

    public function __construct(protected ExecutiveIntelligenceService $service) {}

    public function index(): View
    {
        $user = auth()->user();
        $villageId = $this->getVillageId($user);

        $topInsights = $this->service->getTopInsights($villageId);
        $villageHighlights = $this->service->getVillageHighlights($villageId);
        $comparativeAnalysis = $this->service->getComparativeAnalysis($villageId);

        return view('analytics.executive-intelligence.index', compact(
            'topInsights', 'villageId',
            'villageHighlights',
            'comparativeAnalysis'
        ));
    }

    public function export(Request $request, string $format)
    {
        $user = auth()->user();
        $villageId = $this->getVillageId($user);

        $topInsights = $this->service->getTopInsights($villageId);
        $villageHighlights = $this->service->getVillageHighlights($villageId);
        $comparativeAnalysis = $this->service->getComparativeAnalysis($villageId);

        $data = compact('topInsights', 'villageHighlights', 'comparativeAnalysis');

        if ($format === 'csv') {
            $filename = 'executive-intelligence-report.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            return response()->stream(function () use ($data) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Type', 'Detail', 'Value']);

                foreach ($data['topInsights'] as $insight) {
                    fputcsv($handle, ['Insight', $insight->title ?? '', $insight->value ?? '']);
                }

                foreach ($data['villageHighlights'] as $highlight) {
                    fputcsv($handle, ['Highlight', $highlight->village_name ?? '', $highlight->summary ?? '']);
                }

                foreach ($data['comparativeAnalysis'] as $analysis) {
                    fputcsv($handle, ['Analysis', $analysis->metric ?? '', $analysis->value ?? '']);
                }

                fclose($handle);
            }, 200, $headers);
        }

        if ($format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('analytics.exports.executive-intel-pdf', $data);
            return $pdf->download('executive-intelligence-report.pdf');
        }

        return back()->with('error', 'Unsupported export format.');
    }

}
