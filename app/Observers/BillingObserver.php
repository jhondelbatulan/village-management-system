<?php

namespace App\Observers;

use App\Models\Billing;
use App\Services\ActivityLogService;
use App\Services\Analytics\PaymentBehaviorService;
use App\Services\Analytics\StatisticsService;

class BillingObserver
{
    public function __construct(
        protected PaymentBehaviorService $behaviorService,
        protected StatisticsService $stats
    ) {}

    public function created(Billing $billing): void
    {
        $this->recalculateHousehold($billing);
        $this->stats->flushCache();
        ActivityLogService::log('Created', 'Billings', "Billing created: {$billing->billing_number}", $billing);
    }

    public function updated(Billing $billing): void
    {
        $this->recalculateHousehold($billing);
        $this->stats->flushCache();
        ActivityLogService::log('Updated', 'Billings', "Billing updated: {$billing->billing_number}", $billing);
    }

    public function deleted(Billing $billing): void
    {
        $this->recalculateHousehold($billing);
        $this->stats->flushCache();
        ActivityLogService::log('Deleted', 'Billings', "Billing deleted: {$billing->billing_number}", $billing);
    }

    protected function recalculateHousehold(Billing $billing): void
    {
        if ($billing->household_id && $billing->village_id) {
            if ($this->behaviorService->isPaymentEnabled($billing->village_id)) {
                $household = $billing->household;
                if ($household) {
                    $this->behaviorService->recalculateHousehold($household);
                }
            }
        }
    }
}
