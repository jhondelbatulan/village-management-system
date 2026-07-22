<?php

namespace App\Services\Analytics;

use App\Models\Household;
use App\Models\Resident;
use App\Models\Village;

class ApprovalAnalyticsService
{
    public function getVillageStats(int $villageId): array
    {
        $pendingHouseholds = Household::where('village_id', $villageId)
            ->whereIn('approval_status', ['pending', 'admin_approved'])->count();
        $pendingResidents = Resident::where('village_id', $villageId)
            ->whereIn('approval_status', ['pending', 'admin_approved'])->count();
        $approvedThisMonth = Household::where('village_id', $villageId)
            ->where('approval_status', 'approved')
            ->where('approved_at', '>=', now()->startOfMonth())->count()
            + Resident::where('village_id', $villageId)
            ->where('approval_status', 'approved')
            ->where('approved_at', '>=', now()->startOfMonth())->count();
        $rejectedThisMonth = Household::where('village_id', $villageId)
            ->where('approval_status', 'rejected')
            ->where('updated_at', '>=', now()->startOfMonth())->count()
            + Resident::where('village_id', $villageId)
            ->where('approval_status', 'rejected')
            ->where('updated_at', '>=', now()->startOfMonth())->count();

        $totalHouseholds = Household::where('village_id', $villageId)->count();
        $totalResidents = Resident::where('village_id', $villageId)->count();
        $approvedHouseholds = Household::where('village_id', $villageId)->where('approval_status', 'approved')->count();
        $approvedResidents = Resident::where('village_id', $villageId)->where('approval_status', 'approved')->count();
        $total = $totalHouseholds + $totalResidents;
        $approved = $approvedHouseholds + $approvedResidents;

        return [
            'pending_households' => $pendingHouseholds,
            'pending_residents' => $pendingResidents,
            'total_pending' => $pendingHouseholds + $pendingResidents,
            'approved_this_month' => $approvedThisMonth,
            'rejected_this_month' => $rejectedThisMonth,
            'approval_rate' => $total > 0 ? round(($approved / $total) * 100, 1) : 0,
        ];
    }

    public function getSystemStats(): array
    {
        $pendingHouseholds = Household::whereIn('approval_status', ['pending', 'admin_approved'])->count();
        $pendingResidents = Resident::whereIn('approval_status', ['pending', 'admin_approved'])->count();

        $totalHouseholds = Household::count();
        $totalResidents = Resident::count();
        $approvedHouseholds = Household::where('approval_status', 'approved')->count();
        $approvedResidents = Resident::where('approval_status', 'approved')->count();
        $total = $totalHouseholds + $totalResidents;
        $approved = $approvedHouseholds + $approvedResidents;

        return [
            'pending_households' => $pendingHouseholds,
            'pending_residents' => $pendingResidents,
            'total_pending' => $pendingHouseholds + $pendingResidents,
            'approval_rate' => $total > 0 ? round(($approved / $total) * 100, 1) : 0,
        ];
    }
}
