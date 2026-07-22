<?php

namespace App\Observers;

use App\Models\Resident;
use App\Services\ActivityLogService;
use App\Services\Analytics\StatisticsService;

class ResidentObserver
{
    public function __construct(protected StatisticsService $stats) {}

    public function created(Resident $resident): void
    {
        $this->stats->flushCache();
        ActivityLogService::log('Created', 'Residents', "Resident created: {$resident->full_name}", $resident);
    }

    public function updated(Resident $resident): void
    {
        $this->stats->flushCache();

        if ($resident->wasChanged('approval_status')) {
            $status = $resident->approval_status;
            $action = match ($status) {
                'approved' => 'Resident Approved',
                'rejected' => 'Resident Rejected',
                default => 'Resident Updated',
            };
            ActivityLogService::log($action, 'Residents', "Resident {$status}: {$resident->full_name}", $resident);
        } else {
            ActivityLogService::log('Updated', 'Residents', "Resident updated: {$resident->full_name}", $resident);
        }
    }

    public function deleted(Resident $resident): void
    {
        $this->stats->flushCache();
        ActivityLogService::log('Deleted', 'Residents', "Resident deleted: {$resident->full_name}", $resident);
    }
}
