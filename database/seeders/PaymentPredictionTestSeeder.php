<?php

namespace Database\Seeders;

use App\Models\Household;
use App\Models\HouseholdPayment;
use App\Models\Billing;
use App\Models\Village;
use App\Services\Analytics\PaymentBehaviorService;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PaymentPredictionTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding payment prediction test data...');

        $comagascas = Village::where('village_code', 'COM')->first();
        if (!$comagascas) {
            $this->command->warn('Comagascas village not found. Skipping.');
            return;
        }

        $households = Household::where('village_id', $comagascas->id)
            ->where('status', 'active')
            ->where('approval_status', 'approved')
            ->get();

        if ($households->isEmpty()) {
            $this->command->warn('No approved households found for Comagascas. Skipping.');
            return;
        }

        $paymentTypes = \App\Models\PaymentType::where('village_id', $comagascas->id)->get();
        if ($paymentTypes->isEmpty()) {
            $this->command->warn('No payment types found for Comagascas. Skipping.');
            return;
        }

        $behaviors = [
            ['months' => 12, 'on_time_rate' => 0.95, 'status' => 'paid'],
            ['months' => 10, 'on_time_rate' => 0.80, 'status' => 'paid'],
            ['months' => 8, 'on_time_rate' => 0.55, 'status' => 'paid'],
            ['months' => 6, 'on_time_rate' => 0.30, 'status' => 'overdue'],
            ['months' => 4, 'on_time_rate' => 0.10, 'status' => 'overdue'],
        ];

        $index = 0;
        foreach ($households->take(5) as $household) {
            $behavior = $behaviors[$index % count($behaviors)];
            $this->createPaymentHistory($household, $paymentTypes->first(), $behavior);
            $index++;
        }

        $behaviorService = app(PaymentBehaviorService::class);
        $behaviorService->recalculateVillage($comagascas->id);

        $this->command->info('Payment prediction test data seeded successfully!');
        $this->command->info("Recalculated scores for " . $households->take(5)->count() . " households.");
    }

    protected function createPaymentHistory(Household $household, $paymentType, array $behavior): void
    {
        $now = Carbon::now();

        for ($i = $behavior['months']; $i >= 1; $i--) {
            $billingMonth = $now->copy()->subMonths($i);
            $dueDate = $billingMonth->copy()->endOfMonth();
            $isOnTime = (mt_rand(1, 100) / 100) <= $behavior['on_time_rate'];

            $billing = Billing::create([
                'billing_number' => 'B-' . strtoupper(uniqid()),
                'household_id' => $household->id,
                'village_id' => $household->village_id,
                'payment_type_id' => $paymentType->id,
                'billing_period' => $billingMonth->format('Y-m'),
                'due_date' => $dueDate->format('Y-m-d'),
                'amount' => $paymentType->default_amount,
                'total_amount' => $paymentType->default_amount,
                'status' => $isOnTime ? 'paid' : $behavior['status'],
                'created_by' => 1,
            ]);

            if ($isOnTime) {
                $paymentDate = $dueDate->copy()->subDays(mt_rand(0, 3));
                HouseholdPayment::create([
                    'receipt_number' => 'R-' . strtoupper(uniqid()),
                    'billing_id' => $billing->id,
                    'household_id' => $household->id,
                    'village_id' => $household->village_id,
                    'payment_type_id' => $paymentType->id,
                    'amount' => $paymentType->default_amount,
                    'total_paid' => $paymentType->default_amount,
                    'payment_date' => $paymentDate->format('Y-m-d'),
                    'due_date' => $dueDate->format('Y-m-d'),
                    'payment_method' => 'cash',
                    'payment_status' => 'paid',
                    'received_by' => 1,
                ]);
            }
        }
    }
}
