<?php

namespace App\Console\Commands;

use App\Services\PaymentService;
use Illuminate\Console\Command;

class ProcessOverdueBillings extends Command
{
    protected $signature = 'payments:process-overdue';

    protected $description = 'Mark overdue billings';

    public function handle(): int
    {
        $paymentService = app(PaymentService::class);

        $overdueCount = $paymentService->updateOverdueBillings();
        $this->info("Marked {$overdueCount} billings as overdue.");

        return Command::SUCCESS;
    }
}
