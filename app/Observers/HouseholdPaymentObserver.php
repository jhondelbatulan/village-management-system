<?php

namespace App\Observers;

use App\Models\HouseholdPayment;
use App\Services\ActivityLogService;
use App\Services\Analytics\PaymentBehaviorService;
use App\Services\Analytics\StatisticsService;

class HouseholdPaymentObserver
{
    public function __construct(
        protected PaymentBehaviorService $behaviorService,
        protected StatisticsService $stats
    ) {}

    public function created(HouseholdPayment $payment): void
    {
        $this->recalculateHousehold($payment);
        $this->stats->flushCache();
        ActivityLogService::log('Payment Recorded', 'Payments', "Payment recorded: ₱" . number_format($payment->total_paid, 2), $payment);
    }

    public function updated(HouseholdPayment $payment): void
    {
        $this->recalculateHousehold($payment);
        $this->stats->flushCache();
        ActivityLogService::log('Payment Updated', 'Payments', "Payment updated: ₱" . number_format($payment->total_paid, 2), $payment);
    }

    public function deleted(HouseholdPayment $payment): void
    {
        $this->recalculateHousehold($payment);
        $this->stats->flushCache();
        ActivityLogService::log('Payment Deleted', 'Payments', "Payment deleted: ₱" . number_format($payment->total_paid, 2), $payment);
    }

    protected function recalculateHousehold(HouseholdPayment $payment): void
    {
        if ($payment->household_id && $payment->village_id) {
            if ($this->behaviorService->isPaymentEnabled($payment->village_id)) {
                $household = $payment->household;
                if ($household) {
                    $this->behaviorService->recalculateHousehold($household);
                }
            }
        }
    }
}
