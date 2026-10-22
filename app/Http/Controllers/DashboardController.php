<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\HouseholdPayment;
use App\Models\Resident;
use App\Models\Village;
use App\Models\Announcement;
use App\Models\Event;
use App\Services\PaymentService;
use App\Services\Analytics\DashboardService;
use App\Services\Analytics\PopulationAnalyticsService;
use App\Services\Analytics\HouseholdAnalyticsService;
use App\Services\Analytics\EducationAnalyticsService;
use App\Services\Analytics\EmploymentAnalyticsService;
use App\Services\Analytics\OccupationAnalyticsService;
use App\Services\Analytics\SkillsAnalyticsService;
use App\Services\Analytics\LivelihoodAnalyticsService;
use App\Services\Analytics\PopulationPyramidService;
use App\Services\Analytics\GovernmentAssistanceAnalyticsService;
use App\Services\Analytics\GovernmentMembershipAnalyticsService;
use App\Services\Analytics\HealthcareAnalyticsService;
use App\Services\Analytics\CommunityDevelopmentIndicatorsService;
use App\Services\Analytics\VillageDevelopmentScoreService;
use App\Services\Analytics\ExecutiveIntelligenceService;
use App\Services\Analytics\DependencyRatioService;
use App\Services\Analytics\PopulationDensityService;
use App\Services\Analytics\GrowthAnalyticsService;
use App\Services\Analytics\PlanningIndicatorService;
use App\Services\Analytics\PaymentAnalyticsService;
use App\Models\PaymentType;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private DashboardService $dashboard,
        private PopulationAnalyticsService $populationAnalytics,
        private HouseholdAnalyticsService $householdAnalytics,
        private EducationAnalyticsService $educationAnalytics,
        private EmploymentAnalyticsService $employmentAnalytics,
        private OccupationAnalyticsService $occupationAnalytics,
        private SkillsAnalyticsService $skillsAnalytics,
        private LivelihoodAnalyticsService $livelihoodAnalytics,
        private PopulationPyramidService $pyramidAnalytics,
        private GovernmentAssistanceAnalyticsService $govAssistance,
        private GovernmentMembershipAnalyticsService $govMembership,
        private HealthcareAnalyticsService $healthcare,
        private CommunityDevelopmentIndicatorsService $communityDev,
        private VillageDevelopmentScoreService $devScore,
        private ExecutiveIntelligenceService $execIntel,
        private DependencyRatioService $dependencyRatio,
        private PopulationDensityService $popDensity,
        private GrowthAnalyticsService $growthAnalytics,
        private PlanningIndicatorService $planning,
        private PaymentAnalyticsService $paymentAnalytics
    ) {}

    public function superAdmin(): View
    {
        $overview = $this->dashboard->getSystemOverview();
        $populationStats = $this->dashboard->getPopulation()->getSystemStats();
        $householdStats = $this->dashboard->getHousehold()->getSystemStats();
        $paymentStats = $this->dashboard->getPayment()->getSystemStats();
        $participationStats = $this->dashboard->getParticipation()->getSystemStats();
        $approvalStats = $this->dashboard->getApproval()->getSystemStats();
        $recentActivity = $this->dashboard->getActivity()->getSystemRecentActivity(15);
        $insights = $this->dashboard->getInsight()->getSystemInsights();
        $villageComparison = $this->dashboard->getPopulation()->getVillageComparison();
        $paymentComparison = $this->dashboard->getPayment()->getVillageComparison();
        $participationComparison = $this->dashboard->getParticipation()->getVillageComparison();

        $recentAnnouncements = Announcement::with('village:id,village_name')
            ->active()
            ->latest()
            ->take(5)
            ->get();

        $upcomingEvents = Event::with('village:id,village_name')
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->where('event_date', '>=', now()->toDateString())
            ->orderBy('event_date')
            ->take(5)
            ->get();

        $pendingHouseholds = Household::with('village', 'submittedBy', 'adminApprovedBy')
            ->where('approval_status', 'admin_approved')
            ->latest()->take(5)->get();

        $pendingResidents = Resident::with('village', 'submittedBy', 'adminApprovedBy')
            ->where('approval_status', 'admin_approved')
            ->latest()->take(5)->get();

        $populationSummary = $this->populationAnalytics->getPopulationSummary();
        $populationInsights = $this->populationAnalytics->getPopulationInsights();
        $genderDist = $this->populationAnalytics->getGenderDistribution();
        $monthlyTrend = $this->populationAnalytics->getMonthlyPopulationTrend(null, 12);

        $householdSummary = $this->householdAnalytics->getHouseholdSummary();
        $householdComposition = $this->householdAnalytics->getCompositionDistribution();
        $householdSizeDist = $this->householdAnalytics->getSizeDistribution();
        $householdInsights = $this->householdAnalytics->getHouseholdInsights();
        $householdVillageComparison = $this->householdAnalytics->getVillageComparison();

        $educationSummary = $this->educationAnalytics->getSummaryStats();
        $employmentSummary = $this->employmentAnalytics->getSummaryStats();

        $occupationSummary = $this->occupationAnalytics->getSummaryStats();
        $occupationTop = $this->occupationAnalytics->getTopOccupations(null, 5);
        $occupationByGender = $this->occupationAnalytics->getOccupationByGender();

        $skillsSummary = $this->skillsAnalytics->getSummaryStats();
        $skillTop = $this->skillsAnalytics->getTopSkills(null, 5);

        $livelihoodSummary = $this->livelihoodAnalytics->getSummaryStats();
        $livelihoodDist = $this->livelihoodAnalytics->getLivelihoodDistribution();
        $incomeStats = $this->livelihoodAnalytics->getIncomeStats();

        $pyramidData = $this->pyramidAnalytics->getPyramidData();
        $pyramidStats = $this->pyramidAnalytics->getSummaryStats();

        $govAssistanceSummary = $this->govAssistance->getSummaryStats();
        $govMembershipSummary = $this->govMembership->getSummaryStats();
        $healthcareSummary = $this->healthcare->getSummaryStats();
        $communityIndicators = $this->communityDev->getIndicators();
        $devScoreData = $this->devScore->getScore();
        $execInsights = $this->execIntel->getTopInsights(null, 8);

        $depSummary = $this->dependencyRatio->getSummaryStats();
        $densitySummary = $this->popDensity->getSummaryStats();
        $growthSummary = $this->growthAnalytics->getSummaryStats();
        $planningInsights = $this->planning->getExecutiveInsights(null, 4);

        $monthlyPaymentTrend = $this->paymentAnalytics->getMonthlyTrend(null, 12);
        $govAssistanceProgramCounts = $this->govAssistance->getProgramCounts();
        $govMembershipCounts = $this->govMembership->getMembershipCounts();
        $averageAge = $this->populationAnalytics->getAverageAge();

        return view('dashboard.super-admin', compact(
            'overview', 'populationStats', 'householdStats', 'paymentStats',
            'participationStats', 'approvalStats', 'recentActivity', 'insights',
            'villageComparison', 'paymentComparison', 'participationComparison',
            'pendingHouseholds', 'pendingResidents',
            'recentAnnouncements', 'upcomingEvents',
            'populationSummary', 'populationInsights', 'genderDist', 'monthlyTrend',
            'householdSummary', 'householdComposition', 'householdSizeDist',
            'householdInsights', 'householdVillageComparison',
            'educationSummary', 'employmentSummary',
            'occupationSummary', 'occupationTop', 'occupationByGender',
            'skillsSummary', 'skillTop',
            'livelihoodSummary', 'livelihoodDist', 'incomeStats',
            'pyramidData', 'pyramidStats',
            'govAssistanceSummary', 'govMembershipSummary', 'healthcareSummary',
            'communityIndicators', 'devScoreData', 'execInsights',
            'depSummary', 'densitySummary', 'growthSummary', 'planningInsights',
            'monthlyPaymentTrend', 'govAssistanceProgramCounts', 'govMembershipCounts',
            'averageAge'
        ));
    }

    public function monthlyCollection(): View
    {
        $system = $this->paymentAnalytics->getSystemStats();

        $rows = Village::where('status', 'active')->orderBy('village_name')->get()->map(function ($village) {
            $stats = $this->paymentAnalytics->getVillageStats($village->id);

            return [
                'village' => $village,
                'this_month' => $stats['this_month_collected'],
                'previous_month' => $stats['previous_month_collected'],
                'change' => $stats['change'],
                'total_collected' => $stats['total_collected'],
            ];
        });

        return view('dashboard.super-admin-monthly-collection', compact('system', 'rows'));
    }

    public function admin(): View
    {
        $user = auth()->user();
        $villageId = $user->village_id;
        $village = Village::find($villageId);

        $populationStats = $this->dashboard->getPopulation()->getVillageStats($villageId);
        $householdStats = $this->dashboard->getHousehold()->getVillageStats($villageId);
        $paymentStats = $this->dashboard->getPayment()->getVillageStats($villageId);
        $participationStats = $this->dashboard->getParticipation()->getVillageStats($villageId);
        $announcementStats = $this->dashboard->getAnnouncement()->getVillageStats($villageId);
        $approvalStats = $this->dashboard->getApproval()->getVillageStats($villageId);
        $monthlyDues = $villageId
            ? $this->paymentService->getMonthlyDuesCollections($villageId)
            : [];
        $recentActivity = $villageId
            ? $this->dashboard->getActivity()->getRecentActivity($villageId, 10)
            : collect();

        $insights = $this->dashboard->getInsight()->getVillageInsights($villageId);

        $pendingHouseholds = Household::with('village', 'submittedBy')
            ->where('village_id', $villageId)
            ->where('approval_status', 'pending')
            ->latest()->take(5)->get();

        $pendingResidents = Resident::with('household', 'village', 'submittedBy')
            ->where('village_id', $villageId)
            ->where('approval_status', 'pending')
            ->latest()->take(5)->get();

        $recentAnnouncements = Announcement::where('village_id', $villageId)
            ->visibleToRole($user->role?->role_slug, $user->id)
            ->active()->latest()->take(5)->get();

        $upcomingEvents = Event::where('village_id', $villageId)
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->where('event_date', '>=', now()->toDateString())
            ->orderBy('event_date')->take(5)->get();

        $ageDistribution = $this->populationAnalytics->getAgeDistribution($villageId);

        return view('dashboard.admin', compact(
            'village', 'populationStats', 'householdStats', 'paymentStats',
            'participationStats', 'announcementStats', 'approvalStats',
            'monthlyDues', 'recentActivity', 'insights', 'pendingHouseholds', 'pendingResidents',
            'recentAnnouncements', 'upcomingEvents', 'ageDistribution'
        ));
    }

    public function secretary(): View
    {
        $user = auth()->user();
        $villageId = $user->village_id;
        $village = Village::with('officers')->find($villageId);

        $populationStats = $this->dashboard->getPopulation()->getVillageStats($villageId);
        $householdStats = $this->dashboard->getHousehold()->getVillageStats($villageId);
        $approvalStats = $this->dashboard->getApproval()->getVillageStats($villageId);
        $announcementStats = $this->dashboard->getAnnouncement()->getVillageStats($villageId);
        $recentActivity = $this->dashboard->getActivity()->getRecentActivityForUser($user->id, 10);
        $insights = $this->dashboard->getInsight()->getVillageInsights($villageId);

        $pendingHouseholds = Household::with('village', 'submittedBy')
            ->where('village_id', $villageId)
            ->whereIn('approval_status', ['pending', 'admin_approved'])
            ->latest()->take(5)->get();

        $pendingResidents = Resident::with('village', 'submittedBy')
            ->where('village_id', $villageId)
            ->whereIn('approval_status', ['pending', 'admin_approved'])
            ->latest()->take(5)->get();

        $recentHouseholds = Household::with('village')
            ->where('village_id', $villageId)
            ->latest()->take(5)->get();

        $recentAnnouncements = Announcement::where('village_id', $villageId)
            ->visibleToRole($user->role?->role_slug, $user->id)
            ->active()->latest()->take(5)->get();

        $upcomingEvents = Event::where('village_id', $villageId)
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->where('event_date', '>=', now()->toDateString())
            ->orderBy('event_date')->take(5)->get();

        $genderDist = $this->populationAnalytics->getGenderDistribution($villageId);

        return view('dashboard.secretary', compact(
            'village', 'populationStats', 'householdStats', 'approvalStats',
            'announcementStats', 'recentActivity', 'insights',
            'pendingHouseholds', 'pendingResidents', 'recentHouseholds',
            'recentAnnouncements', 'upcomingEvents', 'genderDist'
        ));
    }

    public function treasurer(): View
    {
        $user = auth()->user();
        $villageId = $user->village_id;
        $village = Village::find($villageId);

        $paymentStats = $this->dashboard->getPayment()->getVillageStats($villageId);
        $recentActivity = $this->dashboard->getActivity()->getRecentActivityForUser($user->id, 10);
        $insights = $this->dashboard->getInsight()->getVillageInsights($villageId);

        $recentPayments = HouseholdPayment::with(['household', 'paymentType'])
            ->where('village_id', $villageId)
            ->latest()->take(10)->get();

        $collectionStats = $this->paymentService->getVillageCollections($villageId);
        $monthlyStats = $this->paymentService->getMonthlyCollections($villageId);
        $monthlyDues = $this->paymentService->getMonthlyDuesCollections($villageId);

        $monthlyPaymentTrend = $this->paymentAnalytics->getMonthlyTrend($villageId, 12);

        $revenueByType = HouseholdPayment::where('household_payments.village_id', $villageId)
            ->where('household_payments.payment_status', 'paid')
            ->join('payment_types', 'household_payments.payment_type_id', '=', 'payment_types.id')
            ->select('payment_types.payment_name', DB::raw('SUM(household_payments.amount) as total'))
            ->groupBy('payment_types.payment_name')
            ->pluck('total', 'payment_name')
            ->toArray();

        $recentAnnouncements = Announcement::where('village_id', $villageId)
            ->visibleToRole($user->role?->role_slug, $user->id)
            ->active()->latest()->take(5)->get();

        $upcomingEvents = Event::where('village_id', $villageId)
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->where('event_date', '>=', now()->toDateString())
            ->orderBy('event_date')->take(5)->get();

        return view('dashboard.treasurer', compact(
            'village', 'paymentStats', 'recentActivity', 'insights',
            'recentPayments', 'collectionStats', 'monthlyStats', 'monthlyDues',
            'monthlyPaymentTrend', 'revenueByType',
            'recentAnnouncements', 'upcomingEvents'
        ));
    }

    public function resident(): View
    {
        $user = auth()->user();
        $household = $user->household;
        $village = $household?->village;

        $balance = $household
            ? $this->paymentService->getHouseholdBalance($household->id)
            : ['outstanding' => 0, 'paid' => 0, 'penalties' => 0];

        $recentAnnouncements = collect();
        $upcomingEvents = collect();
        $insights = [];

        if ($village) {
            $recentAnnouncements = Announcement::where('village_id', $village->id)
                ->visibleToRole('resident', $user->id)
                ->active()->orderByDesc('is_pinned')->latest()->take(5)->get();

            $upcomingEvents = Event::where('village_id', $village->id)
                ->whereIn('status', ['upcoming', 'ongoing'])
                ->where('event_date', '>=', now()->toDateString())
                ->orderBy('event_date')->take(5)->get();

            $insights = $this->dashboard->getInsight()->getVillageInsights($village->id);
        }

        $recentActivity = $this->dashboard->getActivity()->getRecentActivityForUser($user->id, 10);

        return view('dashboard.resident', compact(
            'user', 'household', 'village', 'balance',
            'recentAnnouncements', 'upcomingEvents', 'insights', 'recentActivity'
        ));
    }
}
