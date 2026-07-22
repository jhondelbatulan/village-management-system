<?php

namespace App\Http\Controllers;

use App\Models\Village;
use App\Services\Analytics\GovernmentAssistanceAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GovernmentAssistanceController extends Controller
{
    use \App\Http\Traits\VillageAccessAware;
    public function __construct(protected GovernmentAssistanceAnalyticsService $service) {}

    public function index(): View
    {
        $user = auth()->user();
        $villageId = $this->getVillageId($user);

        $summary = $this->service->getSummaryStats($villageId);
        $programDistribution = $this->service->getProgramDistribution($villageId);
        $programCounts = $this->service->getProgramCounts($villageId);
        $beneficiaryDistribution = $this->service->getBeneficiaryDistribution($villageId);
        $villageComparison = !$villageId ? $this->service->getVillageComparison() : [];
        $insights = $this->service->getInsights($villageId);
        $villages = Village::where('status', 'active')->get();

        return view('analytics.government-assistance.index', compact(
            'summary', 'villageId', 'villages',
            'programDistribution',
            'programCounts',
            'beneficiaryDistribution',
            'villageComparison',
            'insights'
        ));
    }

    public function village(int $villageId): View
    {
        $this->enforceVillageAccess(auth()->user(), $villageId);

        $village = Village::findOrFail($villageId);

        $summary = $this->service->getSummaryStats($villageId);
        $programDistribution = $this->service->getProgramDistribution($villageId);
        $programCounts = $this->service->getProgramCounts($villageId);
        $beneficiaryDistribution = $this->service->getBeneficiaryDistribution($villageId);
        $insights = $this->service->getInsights($villageId);

        return view('analytics.government-assistance.village', compact(
            'village',
            'summary',
            'programDistribution',
            'programCounts',
            'beneficiaryDistribution',
            'insights'
        ));
    }

    public function export(Request $request, string $format)
    {
        $user = auth()->user();
        $villageId = $this->getVillageId($user);

        $summary = $this->service->getSummaryStats($villageId);
        $programDistribution = $this->service->getProgramDistribution($villageId);
        $programCounts = $this->service->getProgramCounts($villageId);
        $beneficiaryDistribution = $this->service->getBeneficiaryDistribution($villageId);
        $insights = $this->service->getInsights($villageId);

        $data = compact('summary', 'programDistribution', 'programCounts', 'beneficiaryDistribution', 'insights');

        if ($format === 'csv') {
            $filename = 'government-assistance-report.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            return response()->stream(function () use ($data) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Metric', 'Value']);
                fputcsv($handle, ['Total Beneficiaries', $data['summary']->total_beneficiaries ?? 0]);
                fputcsv($handle, ['Total Programs', $data['summary']->total_programs ?? 0]);
                fputcsv($handle, ['Total Amount Disbursed', $data['summary']->total_amount_disbursed ?? 0]);

                foreach ($data['programDistribution'] as $item) {
                    fputcsv($handle, [$item->program_name, $item->count]);
                }

                fclose($handle);
            }, 200, $headers);
        }

        if ($format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('analytics.exports.gov-assistance-pdf', $data);
            return $pdf->download('government-assistance-report.pdf');
        }

        return back()->with('error', 'Unsupported export format.');
    }

}
