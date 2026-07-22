<?php

namespace App\Http\Controllers;

use App\Models\Village;
use App\Services\Analytics\SocialWelfareAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SocialWelfareController extends Controller
{
    use \App\Http\Traits\VillageAccessAware;
    public function __construct(protected SocialWelfareAnalyticsService $service) {}

    public function index(): View
    {
        $user = auth()->user();
        $villageId = $this->getVillageId($user);

        $summary = $this->service->getSummaryStats($villageId);
        $categoryDistribution = $this->service->getCategoryDistribution($villageId);
        $villageComparison = !$villageId ? $this->service->getVillageComparison() : [];
        $trend = $this->service->getTrend($villageId);
        $insights = $this->service->getInsights($villageId);
        $villages = Village::where('status', 'active')->get();

        return view('analytics.social-welfare.index', compact(
            'summary', 'villageId', 'villages',
            'categoryDistribution',
            'villageComparison',
            'trend',
            'insights'
        ));
    }

    public function village(int $villageId): View
    {
        $this->enforceVillageAccess(auth()->user(), $villageId);

        $village = Village::findOrFail($villageId);

        $summary = $this->service->getSummaryStats($villageId);
        $categoryDistribution = $this->service->getCategoryDistribution($villageId);
        $trend = $this->service->getTrend($villageId);
        $insights = $this->service->getInsights($villageId);

        return view('analytics.social-welfare.village', compact(
            'village',
            'summary',
            'categoryDistribution',
            'trend',
            'insights'
        ));
    }

    public function export(Request $request, string $format)
    {
        $user = auth()->user();
        $villageId = $this->getVillageId($user);

        $summary = $this->service->getSummaryStats($villageId);
        $categoryDistribution = $this->service->getCategoryDistribution($villageId);
        $trend = $this->service->getTrend($villageId);
        $insights = $this->service->getInsights($villageId);

        $data = compact('summary', 'categoryDistribution', 'trend', 'insights');

        if ($format === 'csv') {
            $filename = 'social-welfare-report.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            return response()->stream(function () use ($data) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Metric', 'Value']);
                fputcsv($handle, ['Total Beneficiaries', $data['summary']->total_beneficiaries ?? 0]);
                fputcsv($handle, ['Total Programs', $data['summary']->total_programs ?? 0]);

                foreach ($data['categoryDistribution'] as $item) {
                    fputcsv($handle, [$item->category, $item->count]);
                }

                fclose($handle);
            }, 200, $headers);
        }

        if ($format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('analytics.exports.social-welfare-pdf', $data);
            return $pdf->download('social-welfare-report.pdf');
        }

        return back()->with('error', 'Unsupported export format.');
    }

}
