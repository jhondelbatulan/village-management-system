<?php

namespace App\Services\Analytics;

use App\Models\Billing;
use App\Models\Household;
use App\Models\HouseholdPayment;
use App\Models\Resident;
use App\Models\Village;

class InsightService
{
    public function getSystemInsights(): array
    {
        $insights = [];

        $currentMonthResidents = Resident::where('status', 'active')
            ->where('created_at', '>=', now()->startOfMonth())->count();
        if ($currentMonthResidents > 0) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'bi-person-plus',
                'message' => "{$currentMonthResidents} new resident(s) registered this month.",
            ];
        }

        $currentMonthPayments = HouseholdPayment::where('created_at', '>=', now()->startOfMonth())->sum('total_paid');
        $prevMonthPayments = HouseholdPayment::where('created_at', '>=', now()->subMonth()->startOfMonth())
            ->where('created_at', '<=', now()->subMonth()->endOfMonth())->sum('total_paid');

        if ($currentMonthPayments > $prevMonthPayments && $prevMonthPayments > 0) {
            $increase = round((($currentMonthPayments - $prevMonthPayments) / $prevMonthPayments) * 100, 1);
            $insights[] = [
                'type' => 'success',
                'icon' => 'bi-graph-up',
                'message' => "Collection improved by {$increase}% compared to last month.",
            ];
        } elseif ($currentMonthPayments < $prevMonthPayments && $prevMonthPayments > 0) {
            $decrease = round((($prevMonthPayments - $currentMonthPayments) / $prevMonthPayments) * 100, 1);
            $insights[] = [
                'type' => 'warning',
                'icon' => 'bi-graph-down',
                'message' => "Collection decreased by {$decrease}% compared to last month.",
            ];
        }

        $pendingCount = Household::whereIn('approval_status', ['pending', 'admin_approved'])->count()
            + Resident::whereIn('approval_status', ['pending', 'admin_approved'])->count();
        if ($pendingCount > 0) {
            $insights[] = [
                'type' => 'info',
                'icon' => 'bi-hourglass-split',
                'message' => "{$pendingCount} approval(s) pending review.",
            ];
        }

        $overdueCount = Billing::where('status', 'overdue')->count();
        if ($overdueCount > 0) {
            $insights[] = [
                'type' => 'danger',
                'icon' => 'bi-exclamation-triangle',
                'message' => "{$overdueCount} billing(s) are overdue.",
            ];
        }

        return $insights;
    }

    public function getVillageInsights(int $villageId): array
    {
        $insights = [];

        $currentMonthResidents = Resident::where('village_id', $villageId)
            ->where('status', 'active')
            ->where('created_at', '>=', now()->startOfMonth())->count();
        if ($currentMonthResidents > 0) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'bi-person-plus',
                'message' => "{$currentMonthResidents} new resident(s) registered this month.",
            ];
        }

        $currentMonthHouseholds = Household::where('village_id', $villageId)
            ->where('created_at', '>=', now()->startOfMonth())->count();
        if ($currentMonthHouseholds > 0) {
            $insights[] = [
                'type' => 'success',
                'icon' => 'bi-house-door',
                'message' => "{$currentMonthHouseholds} new household(s) registered this month.",
            ];
        }

        $currentMonthPayments = HouseholdPayment::where('village_id', $villageId)
            ->where('created_at', '>=', now()->startOfMonth())->sum('total_paid');
        $prevMonthPayments = HouseholdPayment::where('village_id', $villageId)
            ->where('created_at', '>=', now()->subMonth()->startOfMonth())
            ->where('created_at', '<=', now()->subMonth()->endOfMonth())->sum('total_paid');

        if ($prevMonthPayments > 0 && $currentMonthPayments > 0) {
            $change = round((($currentMonthPayments - $prevMonthPayments) / $prevMonthPayments) * 100, 1);
            if ($change > 0) {
                $insights[] = [
                    'type' => 'success',
                    'icon' => 'bi-graph-up',
                    'message' => "Payment collection improved by {$change}% this month.",
                ];
            } elseif ($change < 0) {
                $insights[] = [
                    'type' => 'warning',
                    'icon' => 'bi-graph-down',
                    'message' => "Payment collection decreased by " . abs($change) . "% this month.",
                ];
            }
        }

        $pendingCount = Household::where('village_id', $villageId)
            ->whereIn('approval_status', ['pending', 'admin_approved'])->count()
            + Resident::where('village_id', $villageId)
            ->whereIn('approval_status', ['pending', 'admin_approved'])->count();
        if ($pendingCount > 0) {
            $insights[] = [
                'type' => 'info',
                'icon' => 'bi-hourglass-split',
                'message' => "{$pendingCount} approval(s) awaiting review in your village.",
            ];
        }

        return $insights;
    }
}
