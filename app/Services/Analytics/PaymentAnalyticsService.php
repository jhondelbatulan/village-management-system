<?php

namespace App\Services\Analytics;

use App\Models\Billing;
use App\Models\HouseholdPayment;
use App\Models\Village;
use App\Support\SqlHelper;

class PaymentAnalyticsService
{
    public function getSystemStats(): array
    {
        $totalCollected = (float) HouseholdPayment::where('payment_status', '!=', 'cancelled')->sum('total_paid');
        $thisMonthCollected = (float) HouseholdPayment::where('payment_status', '!=', 'cancelled')
            ->where('payment_date', '>=', now()->startOfMonth())->sum('total_paid');
        $previousMonthCollected = (float) HouseholdPayment::where('payment_status', '!=', 'cancelled')
            ->where('payment_date', '>=', now()->subMonth()->startOfMonth())
            ->where('payment_date', '<=', now()->subMonth()->endOfMonth())->sum('total_paid');

        $totalOutstanding = (float) Billing::whereIn('status', ['pending', 'overdue', 'partially_paid'])->sum('total_amount');
        $overdueCount = Billing::where('status', 'overdue')->count();

        return [
            'total_collected' => $totalCollected,
            'collected' => $totalCollected,
            'this_month_collected' => $thisMonthCollected,
            'previous_month_collected' => $previousMonthCollected,
            'previous' => $previousMonthCollected,
            'total_outstanding' => $totalOutstanding,
            'outstanding' => $totalOutstanding,
            'overdue_count' => $overdueCount,
            'total_billings' => Billing::count(),
            'collection_rate' => $totalOutstanding + $totalCollected > 0
                ? round(($totalCollected / ($totalCollected + $totalOutstanding)) * 100, 1)
                : 0,
            'change' => $previousMonthCollected > 0 ? round(($thisMonthCollected / $previousMonthCollected) * 100 - 100, 1) : 0,
        ];
    }

    public function getVillageStats(int $villageId): array
    {
        $totalCollected = (float) HouseholdPayment::where('village_id', $villageId)
            ->where('payment_status', '!=', 'cancelled')->sum('total_paid');
        $thisMonthCollected = (float) HouseholdPayment::where('village_id', $villageId)
            ->where('payment_status', '!=', 'cancelled')
            ->where('payment_date', '>=', now()->startOfMonth())->sum('total_paid');
        $previousMonthCollected = (float) HouseholdPayment::where('village_id', $villageId)
            ->where('payment_status', '!=', 'cancelled')
            ->where('payment_date', '>=', now()->subMonth()->startOfMonth())
            ->where('payment_date', '<=', now()->subMonth()->endOfMonth())->sum('total_paid');

        $totalOutstanding = (float) Billing::where('village_id', $villageId)
            ->whereIn('status', ['pending', 'overdue', 'partially_paid'])->sum('total_amount');
        $overdueCount = Billing::where('village_id', $villageId)->where('status', 'overdue')->count();

        $totalPaid = Billing::where('village_id', $villageId)->where('status', 'paid')->count();
        $totalUnpaid = Billing::where('village_id', $villageId)->whereIn('status', ['pending', 'overdue'])->count();

        $totalBillings = Billing::where('village_id', $villageId)->count();

        return [
            'total_collected' => $totalCollected,
            'collected' => $totalCollected,
            'this_month_collected' => $thisMonthCollected,
            'previous_month_collected' => $previousMonthCollected,
            'previous' => $previousMonthCollected,
            'total_outstanding' => $totalOutstanding,
            'outstanding' => $totalOutstanding,
            'overdue_count' => $overdueCount,
            'total_billings' => $totalBillings,
            'households_paid' => $totalPaid,
            'households_unpaid' => $totalUnpaid,
            'collection_rate' => $totalOutstanding + $totalCollected > 0
                ? round(($totalCollected / ($totalCollected + $totalOutstanding)) * 100, 1)
                : 0,
            'change' => $previousMonthCollected > 0 ? round(($thisMonthCollected / $previousMonthCollected) * 100 - 100, 1) : 0,
        ];
    }

    public function getMonthlyTrend(?int $villageId = null, int $months = 12): array
    {
        $query = HouseholdPayment::where('created_at', '>=', now()->subMonths($months)->startOfMonth());

        if ($villageId) {
            $query->where('village_id', $villageId);
        }

        $data = $query->selectRaw(
            SqlHelper::dateFormat('created_at', '%Y-%m').' as month, SUM(total_paid) as total'
        )->groupBy('month')->pluck('total', 'month')->toArray();

        $result = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $result[$key] = (float) ($data[$key] ?? 0);
        }

        return $result;
    }

    public function getVillageComparison(): array
    {
        $villages = Village::where('status', 'active')->get();

        return $villages->map(function ($village) {
            $stats = $this->getVillageStats($village->id);

            return [
                'village' => $village,
                'collected' => $stats['total_collected'],
                'outstanding' => $stats['total_outstanding'],
                'change' => $stats['change'],
            ];
        })->toArray();
    }
}
