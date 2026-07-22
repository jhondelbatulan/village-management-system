<?php

namespace App\Observers;

use App\Models\Household;
use App\Services\ActivityLogService;
use App\Services\Analytics\StatisticsService;

class HouseholdObserver
{
    public function __construct(protected StatisticsService $stats) {}

    public function created(Household $household): void
    {
        $this->stats->flushCache();
        ActivityLogService::log('Created', 'Households', "Household created: {$household->household_number}", $household);
    }

    public function updated(Household $household): void
    {
        $this->stats->flushCache();

        if ($household->wasChanged('approval_status')) {
            $status = $household->approval_status;
            $action = match ($status) {
                'approved' => 'Household Approved',
                'rejected' => 'Household Rejected',
                default => 'Household Updated',
            };
            ActivityLogService::log($action, 'Households', "Household {$status}: {$household->household_number}", $household);
        } else {
            ActivityLogService::log('Updated', 'Households', "Household updated: {$household->household_number}", $household);
        }
    }

    public function deleted(Household $household): void
    {
        $this->stats->flushCache();
        ActivityLogService::log('Deleted', 'Households', "Household deleted: {$household->household_number}", $household);
    }
}
