<?php

namespace App\Services\Analytics;

use App\Models\Billing;
use App\Models\Household;
use App\Models\HouseholdPayment;
use App\Models\Resident;
use App\Models\Village;

class DashboardService
{
    protected PopulationAnalyticsService $population;
    protected HouseholdAnalyticsService $household;
    protected PaymentAnalyticsService $payment;
    protected ParticipationAnalyticsService $participation;
    protected AnnouncementAnalyticsService $announcement;
    protected ApprovalAnalyticsService $approval;
    protected ActivityService $activity;
    protected InsightService $insight;

    public function __construct(
        PopulationAnalyticsService $population,
        HouseholdAnalyticsService $household,
        PaymentAnalyticsService $payment,
        ParticipationAnalyticsService $participation,
        AnnouncementAnalyticsService $announcement,
        ApprovalAnalyticsService $approval,
        ActivityService $activity,
        InsightService $insight
    ) {
        $this->population = $population;
        $this->household = $household;
        $this->payment = $payment;
        $this->participation = $participation;
        $this->announcement = $announcement;
        $this->approval = $approval;
        $this->activity = $activity;
        $this->insight = $insight;
    }

    public function getPopulation(): PopulationAnalyticsService
    {
        return $this->population;
    }

    public function getHousehold(): HouseholdAnalyticsService
    {
        return $this->household;
    }

    public function getPayment(): PaymentAnalyticsService
    {
        return $this->payment;
    }

    public function getParticipation(): ParticipationAnalyticsService
    {
        return $this->participation;
    }

    public function getAnnouncement(): AnnouncementAnalyticsService
    {
        return $this->announcement;
    }

    public function getApproval(): ApprovalAnalyticsService
    {
        return $this->approval;
    }

    public function getActivity(): ActivityService
    {
        return $this->activity;
    }

    public function getInsight(): InsightService
    {
        return $this->insight;
    }

    public function getSystemOverview(): array
    {
        $totalVillages = Village::where('status', 'active')->count();
        $totalResidents = Resident::where('status', 'active')->count();
        $totalHouseholds = Household::count();
        $totalUsers = \App\Models\User::where('status', 'active')->count();

        $prevMonthResidents = Resident::where('status', 'active')
            ->where('created_at', '<=', now()->subMonth()->endOfMonth())
            ->count();
        $prevMonthHouseholds = Household::where('created_at', '<=', now()->subMonth()->endOfMonth())->count();

        $totalCollected = (float) HouseholdPayment::sum('total_paid');
        $billedAmount = (float) Billing::whereIn('status', ['pending', 'overdue', 'partially_paid'])->sum('total_amount');
        $totalOutstanding = max(0, $billedAmount - $totalCollected);

        $pendingApprovals = Household::whereIn('approval_status', ['pending', 'admin_approved'])->count()
            + Resident::whereIn('approval_status', ['pending', 'admin_approved'])->count();

        $totalEvents = \App\Models\Event::count();
        $totalAnnouncements = \App\Models\Announcement::count();

        $mappedHouseholds = Household::where('status', 'active')
            ->whereNotNull('latitude')->whereNotNull('longitude')->count();
        $villagesWithGIS = Village::where('status', 'active')
            ->whereNotNull('latitude')->whereNotNull('longitude')->count();

        return [
            'total_villages' => $totalVillages,
            'total_residents' => $totalResidents,
            'total_households' => $totalHouseholds,
            'total_users' => $totalUsers,
            'prev_month_residents' => $prevMonthResidents,
            'prev_month_households' => $prevMonthHouseholds,
            'total_collected' => $totalCollected,
            'total_outstanding' => $totalOutstanding,
            'pending_approvals' => $pendingApprovals,
            'total_events' => $totalEvents,
            'total_announcements' => $totalAnnouncements,
            'mapped_households' => $mappedHouseholds,
            'villages_mapped' => $villagesWithGIS,
            'coverage_pct' => $totalVillages > 0 ? round(($villagesWithGIS / $totalVillages) * 100, 1) : 0,
        ];
    }

    public function getVillageOverview(int $villageId): array
    {
        return [
            'population' => $this->population->getVillageStats($villageId),
            'households' => $this->household->getVillageStats($villageId),
            'payments' => $this->payment->getVillageStats($villageId),
            'participation' => $this->participation->getVillageStats($villageId),
            'announcements' => $this->announcement->getVillageStats($villageId),
            'approvals' => $this->approval->getVillageStats($villageId),
        ];
    }
}
