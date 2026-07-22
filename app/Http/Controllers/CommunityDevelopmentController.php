<?php

namespace App\Http\Controllers;

use App\Models\Village;
use App\Services\Analytics\CommunityDevelopmentIndicatorsService;
use App\Services\Analytics\CommunityReadinessService;
use App\Services\Analytics\SocioEconomicHealthScoreService;
use App\Services\Analytics\VillageDevelopmentScoreService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunityDevelopmentController extends Controller
{
    use \App\Http\Traits\VillageAccessAware;
    public function __construct(
        protected CommunityDevelopmentIndicatorsService $indicatorsService,
        protected VillageDevelopmentScoreService $devScoreService,
        protected SocioEconomicHealthScoreService $socioScoreService,
        protected CommunityReadinessService $readinessService
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $villageId = $this->getVillageId($user);

        $indicators = $this->indicatorsService->getIndicators($villageId);
        $devScore = $this->devScoreService->getScore($villageId);
        $socioScore = $this->socioScoreService->getScore($villageId);
        $readiness = $this->readinessService->getScore($villageId);
        $villageRankings = $this->devScoreService->getVillageRankings();
        $insights = $this->indicatorsService->getInsights($villageId);

        return view('analytics.community-development.index', compact(
            'indicators', 'villageId',
            'devScore',
            'socioScore',
            'readiness',
            'villageRankings',
            'insights'
        ));
    }

    public function village(int $villageId): View
    {
        $this->enforceVillageAccess(auth()->user(), $villageId);

        $village = Village::findOrFail($villageId);

        $indicators = $this->indicatorsService->getIndicators($villageId);
        $devScore = $this->devScoreService->getScore($villageId);
        $socioScore = $this->socioScoreService->getScore($villageId);
        $readiness = $this->readinessService->getScore($villageId);
        $insights = $this->indicatorsService->getInsights($villageId);

        return view('analytics.community-development.village', compact(
            'village',
            'indicators',
            'devScore',
            'socioScore',
            'readiness',
            'insights'
        ));
    }

    public function export(Request $request, string $format)
    {
        $user = auth()->user();
        $villageId = $this->getVillageId($user);

        $indicators = $this->indicatorsService->getIndicators($villageId);
        $devScore = $this->devScoreService->getScore($villageId);
        $socioScore = $this->socioScoreService->getScore($villageId);
        $readiness = $this->readinessService->getScore($villageId);
        $insights = $this->indicatorsService->getInsights($villageId);

        $data = compact('indicators', 'devScore', 'socioScore', 'readiness', 'insights');

        if ($format === 'csv') {
            $filename = 'community-development-report.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            return response()->stream(function () use ($data) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Metric', 'Value']);
                fputcsv($handle, ['Development Score', $data['devScore']->score ?? 0]);
                fputcsv($handle, ['Socio-Economic Health Score', $data['socioScore']->score ?? 0]);
                fputcsv($handle, ['Community Readiness Score', $data['readiness']->score ?? 0]);

                foreach ($data['indicators'] as $item) {
                    fputcsv($handle, [$item->name, $item->value]);
                }

                fclose($handle);
            }, 200, $headers);
        }

        if ($format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('analytics.exports.community-dev-pdf', $data);
            return $pdf->download('community-development-report.pdf');
        }

        return back()->with('error', 'Unsupported export format.');
    }

}
