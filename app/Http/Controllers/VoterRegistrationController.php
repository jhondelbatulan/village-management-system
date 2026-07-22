<?php

namespace App\Http\Controllers;

use App\Models\Village;
use App\Services\Analytics\VoterRegistrationAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VoterRegistrationController extends Controller
{
    use \App\Http\Traits\VillageAccessAware;
    public function __construct(protected VoterRegistrationAnalyticsService $service) {}

    public function index(): View
    {
        $user = auth()->user();
        $villageId = $this->getVillageId($user);

        $summary = $this->service->getSummaryStats($villageId);
        $registrationByGender = $this->service->getRegistrationByGender($villageId);
        $villageComparison = !$villageId ? $this->service->getVillageComparison() : [];
        $insights = $this->service->getInsights($villageId);
        $villages = Village::where('status', 'active')->get();

        return view('analytics.voter-registration.index', compact(
            'summary', 'villageId', 'villages',
            'registrationByGender',
            'villageComparison',
            'insights'
        ));
    }

    public function village(int $villageId): View
    {
        $this->enforceVillageAccess(auth()->user(), $villageId);

        $village = Village::findOrFail($villageId);

        $summary = $this->service->getSummaryStats($villageId);
        $registrationByGender = $this->service->getRegistrationByGender($villageId);
        $insights = $this->service->getInsights($villageId);

        return view('analytics.voter-registration.village', compact(
            'village',
            'summary',
            'registrationByGender',
            'insights'
        ));
    }

    public function export(Request $request, string $format)
    {
        $user = auth()->user();
        $villageId = $this->getVillageId($user);

        $summary = $this->service->getSummaryStats($villageId);
        $registrationByGender = $this->service->getRegistrationByGender($villageId);
        $insights = $this->service->getInsights($villageId);

        $data = compact('summary', 'registrationByGender', 'insights');

        if ($format === 'csv') {
            $filename = 'voter-registration-report.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            return response()->stream(function () use ($data) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Metric', 'Value']);
                fputcsv($handle, ['Total Registered', $data['summary']->total_registered ?? 0]);
                fputcsv($handle, ['Registration Rate', $data['summary']->registration_rate ?? 0]);

                foreach ($data['registrationByGender'] as $item) {
                    fputcsv($handle, [$item->gender, $item->count]);
                }

                fclose($handle);
            }, 200, $headers);
        }

        if ($format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('analytics.exports.voter-pdf', $data);
            return $pdf->download('voter-registration-report.pdf');
        }

        return back()->with('error', 'Unsupported export format.');
    }

}
