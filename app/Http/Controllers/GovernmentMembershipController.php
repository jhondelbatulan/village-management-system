<?php

namespace App\Http\Controllers;

use App\Models\Village;
use App\Services\Analytics\GovernmentMembershipAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GovernmentMembershipController extends Controller
{
    use \App\Http\Traits\VillageAccessAware;
    public function __construct(protected GovernmentMembershipAnalyticsService $service) {}

    public function index(): View
    {
        $user = auth()->user();
        $villageId = $this->getVillageId($user);

        $summary = $this->service->getSummaryStats($villageId);
        $membershipDistribution = $this->service->getMembershipDistribution($villageId);
        $membershipCounts = $this->service->getMembershipCounts($villageId);
        $villageComparison = !$villageId ? $this->service->getVillageComparison() : [];
        $insights = $this->service->getInsights($villageId);
        $villages = Village::where('status', 'active')->get();

        return view('analytics.government-membership.index', compact(
            'summary', 'villageId', 'villages',
            'membershipDistribution',
            'membershipCounts',
            'villageComparison',
            'insights'
        ));
    }

    public function village(int $villageId): View
    {
        $this->enforceVillageAccess(auth()->user(), $villageId);

        $village = Village::findOrFail($villageId);

        $summary = $this->service->getSummaryStats($villageId);
        $membershipDistribution = $this->service->getMembershipDistribution($villageId);
        $membershipCounts = $this->service->getMembershipCounts($villageId);
        $insights = $this->service->getInsights($villageId);

        return view('analytics.government-membership.village', compact(
            'village',
            'summary',
            'membershipDistribution',
            'membershipCounts',
            'insights'
        ));
    }

    public function export(Request $request, string $format)
    {
        $user = auth()->user();
        $villageId = $this->getVillageId($user);

        $summary = $this->service->getSummaryStats($villageId);
        $membershipDistribution = $this->service->getMembershipDistribution($villageId);
        $membershipCounts = $this->service->getMembershipCounts($villageId);
        $insights = $this->service->getInsights($villageId);

        $data = compact('summary', 'membershipDistribution', 'membershipCounts', 'insights');

        if ($format === 'csv') {
            $filename = 'government-membership-report.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            return response()->stream(function () use ($data) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Metric', 'Value']);
                fputcsv($handle, ['Total Members', $data['summary']->total_members ?? 0]);
                fputcsv($handle, ['Total Organizations', $data['summary']->total_organizations ?? 0]);

                foreach ($data['membershipDistribution'] as $item) {
                    fputcsv($handle, [$item->organization_name, $item->count]);
                }

                fclose($handle);
            }, 200, $headers);
        }

        if ($format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('analytics.exports.gov-membership-pdf', $data);
            return $pdf->download('government-membership-report.pdf');
        }

        return back()->with('error', 'Unsupported export format.');
    }

}
