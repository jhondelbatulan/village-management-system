<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\Village;
use App\Services\Analytics\PaymentBehaviorService;
use App\Services\Analytics\PaymentReliabilityService;
use App\Services\Analytics\PaymentRecommendationService;
use App\Services\Analytics\PredictionAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PredictivePaymentController extends Controller
{
    use \App\Http\Traits\VillageAccessAware;

    public function __construct(
        protected PaymentBehaviorService $behaviorService,
        protected PaymentReliabilityService $reliabilityService,
        protected PaymentRecommendationService $recommendationService,
        protected PredictionAnalyticsService $predictionAnalytics
    ) {}

    public function index(Request $request): View
    {
        $villageId = $this->resolveVillageId($request);

        if ($villageId) {
            $village = Village::findOrFail($villageId);
            $stats = $this->predictionAnalytics->getVillageStats($villageId);
            $distribution = $this->reliabilityService->getVillageDistribution($villageId);
            $scoreDistribution = $this->reliabilityService->getScoreDistribution($villageId);
            $insights = $this->predictionAnalytics->getInsights($villageId);
            $monthlyTrend = $this->predictionAnalytics->getMonthlyTrend($villageId, 12);
            $consistencyTrend = $this->predictionAnalytics->getConsistencyTrend($villageId, 12);
            $recommendations = $this->recommendationService->getRecommendationSummary($villageId);
            $topPerformers = $this->reliabilityService->getTopPerformers($villageId, 5);
            $atRiskHouseholds = $this->reliabilityService->getAtRiskHouseholds($villageId, 5);
            $avgScore = $this->reliabilityService->getAverageScore($villageId);
            $villages = Village::where('status', 'active')
                ->whereHas('paymentConfiguration', fn($q) => $q->where('payment_enabled', true))
                ->orderByRaw("CASE WHEN village_code = 'LAU' THEN 0 ELSE 1 END")
                ->orderBy('village_name')
                ->get();
        } else {
            $village = null;
            $stats = $this->predictionAnalytics->getSystemStats();
            $distribution = $this->reliabilityService->getSystemDistribution();
            $scoreDistribution = null;
            $insights = [];
            $monthlyTrend = [];
            $consistencyTrend = [];
            $recommendations = [];
            $topPerformers = collect();
            $atRiskHouseholds = collect();
            $avgScore = $this->reliabilityService->getSystemAverageScore();
            $villages = Village::where('status', 'active')
                ->whereHas('paymentConfiguration', fn($q) => $q->where('payment_enabled', true))
                ->orderByRaw("CASE WHEN village_code = 'LAU' THEN 0 ELSE 1 END")
                ->orderBy('village_name')
                ->get();

            foreach ($villages as $v) {
                $insights = array_merge($insights, $this->predictionAnalytics->getInsights($v->id));
            }
        }

        return view('analytics.predictive-payment.index', compact(
            'village', 'villageId', 'stats', 'distribution', 'scoreDistribution',
            'insights', 'monthlyTrend', 'consistencyTrend', 'recommendations',
            'topPerformers', 'atRiskHouseholds', 'avgScore', 'villages'
        ));
    }

    public function village(int $villageId): View
    {
        $user = auth()->user();
        $this->enforceVillageAccess($user, $villageId);

        $village = Village::findOrFail($villageId);
        $stats = $this->predictionAnalytics->getVillageStats($villageId);
        $distribution = $this->reliabilityService->getVillageDistribution($villageId);
        $scoreDistribution = $this->reliabilityService->getScoreDistribution($villageId);
        $insights = $this->predictionAnalytics->getInsights($villageId);
        $monthlyTrend = $this->predictionAnalytics->getMonthlyTrend($villageId, 12);
        $consistencyTrend = $this->predictionAnalytics->getConsistencyTrend($villageId, 12);
        $recommendations = $this->recommendationService->getRecommendationSummary($villageId);
        $topPerformers = $this->reliabilityService->getTopPerformers($villageId, 10);
        $atRiskHouseholds = $this->reliabilityService->getAtRiskHouseholds($villageId, 10);
        $avgScore = $this->reliabilityService->getAverageScore($villageId);
        $villages = Village::where('status', 'active')
            ->whereHas('paymentConfiguration', fn($q) => $q->where('payment_enabled', true))
            ->get();

        return view('analytics.predictive-payment.village', compact(
            'village', 'villageId', 'stats', 'distribution', 'scoreDistribution',
            'insights', 'monthlyTrend', 'consistencyTrend', 'recommendations',
            'topPerformers', 'atRiskHouseholds', 'avgScore', 'villages'
        ));
    }

    public function household(Household $household): View
    {
        $village = $household->village;

        if (!$this->behaviorService->isPaymentEnabled($village->id)) {
            return redirect()->route('households.show', $household)
                ->with('error', 'Payment module is not enabled for this village.');
        }

        if (is_null($household->payment_behavior)) {
            $this->behaviorService->recalculateHousehold($household);
            $household->refresh();
        }

        $prediction = $this->behaviorService->calculateScore($household);
        $recommendation = $this->recommendationService->getRecommendation($household);

        $payments = \App\Models\HouseholdPayment::where('household_id', $household->id)
            ->where('village_id', $village->id)
            ->where('payment_status', '!=', 'cancelled')
            ->latest('payment_date')
            ->get();

        $billings = \App\Models\Billing::where('household_id', $household->id)
            ->where('village_id', $village->id)
            ->where('status', '!=', 'cancelled')
            ->latest('due_date')
            ->get();

        $paymentHistory = [
            'total_billings' => $billings->count(),
            'total_payments' => $payments->count(),
            'on_time' => $payments->filter(fn($p) => $p->payment_date && $p->due_date && $p->payment_date <= $p->due_date)->count(),
            'late' => $payments->filter(fn($p) => $p->payment_date && $p->due_date && $p->payment_date > $p->due_date && $p->payment_status === 'paid')->count(),
            'missed' => $billings->filter(fn($b) => $b->status === 'overdue' || ($b->status === 'pending' && \Carbon\Carbon::parse($b->due_date)->isPast()))->count(),
            'partial' => $payments->where('payment_status', 'partially_paid')->count(),
            'total_paid' => round($payments->where('payment_status', 'paid')->sum('total_paid'), 2),
            'total_outstanding' => round($household->outstanding_balance, 2),
            'avg_payment_amount' => $payments->count() > 0
                ? round($payments->where('payment_status', 'paid')->avg('total_paid'), 2)
                : 0,
        ];

        return view('analytics.predictive-payment.household', compact(
            'household', 'village', 'prediction', 'recommendation',
            'payments', 'billings', 'paymentHistory'
        ));
    }

    public function export(Request $request, string $format): \Symfony\Component\HttpFoundation\Response
    {
        $villageId = $this->resolveVillageId($request);
        $villageName = $villageId ? Village::find($villageId)?->village_name : 'System-Wide';

        $households = Household::where('status', 'active')
            ->where('approval_status', 'approved')
            ->whereNotNull('payment_behavior')
            ->when($villageId, fn($q) => $q->where('village_id', $villageId))
            ->orderBy('payment_reliability_score', 'desc')
            ->get();

        $data = [
            'title' => 'Payment Prediction Report',
            'villageName' => $villageName,
            'generatedAt' => now()->format('F d, Y h:i A'),
            'households' => $households->map(fn($h) => [
                'household_number' => $h->household_number,
                'head_name' => $h->household_head_name,
                'block_lot' => $h->block_number . '/' . $h->lot_number,
                'behavior' => $h->payment_behavior,
                'behavior_label' => \App\Services\Analytics\PaymentBehaviorService::BEHAVIOR_LABELS[$h->payment_behavior] ?? 'Unknown',
                'score' => $h->payment_reliability_score,
                'confidence' => $h->prediction_confidence,
            ])->toArray(),
            'stats' => $villageId
                ? $this->predictionAnalytics->getVillageStats($villageId)
                : $this->predictionAnalytics->getSystemStats(),
        ];

        if ($format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.exports.predictive-payment-pdf', $data);
            return $pdf->download("payment-prediction-report-{$villageName}-" . now()->format('Y-m-d') . ".pdf");
        }

        return response()->stream(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Payment Prediction Report - ' . $data['villageName']]);
            fputcsv($handle, ['Generated: ' . $data['generatedAt']]);
            fputcsv($handle, []);
            fputcsv($handle, ['Household #', 'Head Name', 'Block/Lot', 'Behavior', 'Score', 'Confidence (%)']);
            foreach ($data['households'] as $h) {
                fputcsv($handle, [
                    $h['household_number'], $h['head_name'], $h['block_lot'],
                    $h['behavior_label'], $h['score'], $h['confidence'],
                ]);
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="payment-prediction-report.csv"',
        ]);
    }

    public function recalculate(Request $request): RedirectResponse
    {
        $villageId = $request->input('village_id');

        if ($villageId) {
            $this->behaviorService->recalculateVillage((int) $villageId);
            return redirect()->back()->with('success', 'Payment predictions recalculated for the selected village.');
        }

        $this->behaviorService->recalculateAll();
        return redirect()->back()->with('success', 'Payment predictions recalculated for all villages.');
    }

    protected function resolveVillageId(Request $request): ?int
    {
        $villageId = $request->input('village_id');

        if ($villageId) {
            $this->enforceVillageAccess(auth()->user(), (int) $villageId);
            return (int) $villageId;
        }

        $user = auth()->user();
        if ($user && $user->role?->role_slug === 'treasurer' && $user->village_id) {
            return $user->village_id;
        }
        if ($user && $user->role?->role_slug === 'admin' && $user->village_id) {
            return $user->village_id;
        }

        return null;
    }

}
