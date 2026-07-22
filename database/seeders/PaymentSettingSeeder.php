<?php

namespace Database\Seeders;

use App\Models\Village;
use App\Models\PaymentConfiguration;
use App\Models\PaymentType;
use Illuminate\Database\Seeder;

class PaymentSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'COM' => [
                'payment_enabled' => true,
                'payment_manager_role' => 'treasurer',
                'penalty_rate' => 5.00,
                'grace_period_days' => 15,
                'billing_prefix' => 'COM-BILL',
                'receipt_prefix' => 'COM-RCPT',
                'permit_prefix' => 'COM-PMT',
                'payment_notes' => 'Monthly rental due every 5th of the month. Dues collected every 5th.',
                'monthly_due_day' => 5,
                'annual_due_month' => null,
                'annual_due_day' => null,
                'types' => [
                    ['payment_name' => 'Monthly House Rental', 'frequency' => 'monthly', 'default_amount' => 500.00, 'description' => 'Monthly rental fee for housing unit.'],
                    ['payment_name' => 'Monthly Village Dues', 'frequency' => 'monthly', 'default_amount' => 200.00, 'description' => 'Monthly community dues for village maintenance.'],
                ],
            ],
            'LAU' => [
                'payment_enabled' => true,
                'payment_manager_role' => 'super_admin',
                'penalty_rate' => 3.00,
                'grace_period_days' => 30,
                'billing_prefix' => 'LAU-BILL',
                'receipt_prefix' => 'LAU-RCPT',
                'permit_prefix' => 'LAU-PMT',
                'payment_notes' => 'Annual building permit managed by CPDO. No monthly rental collected. Amounts vary by building size.',
                'monthly_due_day' => null,
                'annual_due_month' => 3,
                'annual_due_day' => 31,
                'types' => [
                    ['payment_name' => 'Annual Building Permit', 'frequency' => 'annual', 'default_amount' => 3000.00, 'description' => 'Annual permit for home improvements, managed by CPDO.'],
                ],
            ],
            'CAL' => [
                'payment_enabled' => false,
                'payment_manager_role' => null,
                'penalty_rate' => 0.00,
                'grace_period_days' => 0,
                'billing_prefix' => 'CAL-BILL',
                'receipt_prefix' => 'CAL-RCPT',
                'permit_prefix' => 'CAL-PMT',
                'payment_notes' => 'Payment module disabled for this village.',
                'monthly_due_day' => null,
                'annual_due_month' => null,
                'annual_due_day' => null,
            ],
        ];

        foreach ($settings as $villageCode => $config) {
            $village = Village::where('village_code', $villageCode)->first();
            if (!$village) {
                $this->command?->warn("Village {$villageCode} not found, skipping...");
                continue;
            }

            $types = $config['types'] ?? [];
            unset($config['types']);

            PaymentConfiguration::updateOrCreate(
                ['village_id' => $village->id],
                array_merge($config, ['village_id' => $village->id])
            );

            foreach ($types as $type) {
                PaymentType::updateOrCreate(
                    ['payment_name' => $type['payment_name'], 'village_id' => $village->id],
                    array_merge($type, ['village_id' => $village->id, 'is_active' => true])
                );
            }

            $this->command?->info("Payment settings seeded for {$village->village_name}.");
        }
    }
}
