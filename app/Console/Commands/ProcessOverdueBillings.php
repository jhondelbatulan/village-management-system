<?php

namespace App\Console\Commands;

use App\Models\Billing;
use App\Models\PaymentConfiguration;
use App\Services\AuditLogService;
use App\Services\PaymentService;
use Illuminate\Console\Command;

class ProcessOverdueBillings extends Command
{
    protected $signature = 'payments:process-overdue {--apply-penalties}';

    protected $description = 'Mark overdue billings and optionally apply penalties';

    public function handle(): int
    {
        $paymentService = app(PaymentService::class);

        $overdueCount = $paymentService->updateOverdueBillings();
        $this->info("Marked {$overdueCount} billings as overdue.");

        if ($this->option('apply-penalties')) {
            $penaltyCount = $this->applyPenalties($paymentService);
            $this->info("Applied penalties to {$penaltyCount} billings.");
        }

        return Command::SUCCESS;
    }

    private function applyPenalties(PaymentService $paymentService): int
    {
        $count = 0;
        $configs = PaymentConfiguration::where('payment_enabled', true)->get();

        foreach ($configs as $config) {
            if ($config->penalty_rate <= 0) {
                continue;
            }

            $overdueBillings = Billing::where('village_id', $config->village_id)
                ->where('status', 'overdue')
                ->whereDoesntHave('penalties', function ($q) {
                    $q->where('status', 'active');
                })
                ->get();

            foreach ($overdueBillings as $billing) {
                $penaltyAmount = $paymentService->calculatePenaltyAmount($billing);

                if ($penaltyAmount > 0) {
                    $paymentService->recordPenalty(
                        $billing,
                        $penaltyAmount,
                        "Automatic penalty: {$config->penalty_rate}% rate, " .
                        now()->diffInDays($billing->due_date) . " days overdue"
                    );
                    $count++;
                }
            }
        }

        AuditLogService::log(
            'Apply Penalties',
            'Payment Management',
            "Applied penalties to {$count} overdue billings"
        );

        return $count;
    }
}
