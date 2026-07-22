<?php

namespace App\Http\Controllers;

use App\Models\Village;
use App\Services\Analytics\SkillsAnalyticsService;
use App\Services\Analytics\SkillsInventoryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SkillsAnalyticsController extends Controller
{
    use \App\Http\Traits\VillageAccessAware;

    public function __construct(
        protected SkillsAnalyticsService $skills,
        protected SkillsInventoryService $inventory
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $villageId = $this->getVillageId($user);

        $summary = $this->skills->getSummaryStats($villageId);
        $primaryDist = $this->skills->getPrimarySkillDistribution($villageId);
        $secondaryDist = $this->skills->getSecondarySkillDistribution($villageId);
        $combinedDist = $this->skills->getCombinedSkillDistribution($villageId);
        $rankings = $this->skills->getSkillRankings($villageId);
        $inventory = $this->inventory->getCategorySummary($villageId);
        $inventoryBreakdown = $this->inventory->getCategoryBreakdown($villageId);
        $insights = $this->skills->getInsights($villageId);

        $villages = Village::where('status', 'active')->get();
        $villageComparison = !$villageId ? $this->skills->getSkillsByVillage() : [];
        $villageInventoryComparison = !$villageId ? $this->inventory->getSkillsByVillage() : [];

        return view('analytics.skills.index', compact(
            'summary', 'primaryDist', 'secondaryDist', 'combinedDist',
            'rankings', 'inventory', 'inventoryBreakdown', 'insights',
            'villages', 'villageComparison', 'villageInventoryComparison', 'villageId'
        ));
    }

    public function village(int $villageId): View
    {
        $this->enforceVillageAccess(auth()->user(), $villageId);

        $village = Village::findOrFail($villageId);
        $summary = $this->skills->getSummaryStats($villageId);
        $primaryDist = $this->skills->getPrimarySkillDistribution($villageId);
        $secondaryDist = $this->skills->getSecondarySkillDistribution($villageId);
        $combinedDist = $this->skills->getCombinedSkillDistribution($villageId);
        $rankings = $this->skills->getSkillRankings($villageId);
        $inventory = $this->inventory->getCategorySummary($villageId);
        $inventoryBreakdown = $this->inventory->getCategoryBreakdown($villageId);
        $insights = $this->skills->getInsights($villageId);

        return view('analytics.skills.village', compact(
            'village', 'summary', 'primaryDist', 'secondaryDist', 'combinedDist',
            'rankings', 'inventory', 'inventoryBreakdown', 'insights'
        ));
    }

    public function export(Request $request, string $format)
    {
        $user = auth()->user();
        $villageId = $this->getVillageId($user);
        $summary = $this->skills->getSummaryStats($villageId);
        $rankings = $this->skills->getSkillRankings($villageId);
        $inventory = $this->inventory->getCategorySummary($villageId);

        if ($format === 'csv') {
            $filename = 'skills_analytics_' . date('Y-m-d') . '.csv';
            $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"{$filename}\""];
            $callback = function () use ($rankings, $summary, $inventory) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Skills Analytics Report', date('F d, Y')]);
                fputcsv($handle, ['Total', $summary['total'], 'With Skill', $summary['with_any_skill'], 'Coverage', $summary['skill_coverage'] . '%']);
                fputcsv($handle, []);
                fputcsv($handle, ['--- Skill Rankings ---']);
                fputcsv($handle, ['Rank', 'Skill', 'Count']);
                foreach ($rankings as $r) {
                    fputcsv($handle, [$r['rank'], $r['skill'], $r['count']]);
                }
                fputcsv($handle, []);
                fputcsv($handle, ['--- Category Breakdown ---']);
                fputcsv($handle, ['Category', 'Count', 'Percentage']);
                foreach ($inventory as $cat => $data) {
                    fputcsv($handle, [$cat, $data['count'], $data['percentage'] . '%']);
                }
                fclose($handle);
            };
            return response()->stream($callback, 200, $headers);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('analytics.exports.skills-pdf', compact('summary', 'rankings', 'inventory'));
        return $pdf->download('skills_analytics_' . date('Y-m-d') . '.pdf');

        return back()->with('error', 'Unsupported export format.');
    }

}
