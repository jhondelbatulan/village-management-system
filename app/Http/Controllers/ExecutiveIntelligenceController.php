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
                fputcsv($handle, ['Type', 'Name', 'Detail', 'Extra']);

                foreach ($data['topInsights'] as $insight) {
                    fputcsv($handle, [
                        'Insight',
                        ucfirst($insight['type'] ?? ''),
                        $insight['message'] ?? '',
                        ($insight['recommendation'] ?? '') . ' [' . ucfirst($insight['priority'] ?? '') . ' priority]',
                    ]);
                }

                foreach ($data['villageHighlights'] as $highlight) {
                    fputcsv($handle, [
                        'Village',
                        $highlight['village']['name'] ?? '',
                        'Population: ' . ($highlight['population'] ?? 0) . '; Employment: ' . ($highlight['employment_rate'] ?? 0) . '%',
                        'Income: ₱' . number_format((float) ($highlight['average_income'] ?? 0), 2)
                            . '; Readiness: ' . ($highlight['readiness_score'] ?? 0) . '/100',
                    ]);
                }

                $analysis = $data['comparativeAnalysis'];
                if (! empty($analysis['villages'])) {
                    foreach ($analysis['villages'] as $metrics) {
                        fputcsv($handle, [
                            'Comparison',
                            $metrics['village_name'] ?? '',
                            'Employment: ' . ($metrics['employment_rate'] ?? 0) . '%; Income: ₱' . number_format((float) ($metrics['average_income'] ?? 0), 2),
                            'Skills: ' . ($metrics['skill_coverage'] ?? 0) . '%; Education: ' . ($metrics['education_rate'] ?? 0) . '%',
                        ]);
                    }
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
