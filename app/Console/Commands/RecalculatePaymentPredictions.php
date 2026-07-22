<?php

namespace App\Console\Commands;

use App\Services\Analytics\PaymentBehaviorService;
use Illuminate\Console\Command;

class RecalculatePaymentPredictions extends Command
{
    protected $signature = 'payments:recalculate-predictions {--village_id= : Recalculate only for a specific village ID}';
    protected $description = 'Recalculate payment behavior and prediction scores for all active households';

    public function handle(PaymentBehaviorService $behaviorService): int
    {
        $villageId = $this->option('village_id');

        if ($villageId) {
            $this->info("Recalculating predictions for village ID {$villageId}...");
            $behaviorService->recalculateVillage((int) $villageId);
            $this->info('Done.');
        } else {
            $this->info('Recalculating predictions for all villages with payment enabled...');
            $behaviorService->recalculateAll();
            $this->info('Done.');
        }

        return Command::SUCCESS;
    }
}
