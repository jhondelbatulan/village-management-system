<?php

namespace App\Services;

use App\Models\Billing;
use App\Models\Household;
use App\Models\HouseholdPayment;
use App\Models\HouseholdPaymentAmount;
use App\Models\PaymentConfiguration;
use App\Models\PaymentPenalty;
use App\Models\PaymentReceipt;
use App\Models\PaymentType;
use App\Support\SqlHelper;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function getVillageSetting(int $villageId): ?PaymentConfiguration
    {
        return PaymentConfiguration::where('village_id', $villageId)->first();
    }

    public function isPaymentEnabled(int $villageId): bool
    {
        return $this->getVillageSetting($villageId)?->payment_enabled ?? false;
    }

    public function getActivePaymentTypes(int $villageId)
    {
        return PaymentType::where('village_id', $villageId)
            ->where('is_active', true)
            ->get();
    }

    public function generateBillings(int $villageId, string $billingPeriod, null|int|array $paymentTypeIds = null): array
    {
        $config = $this->getVillageSetting($villageId);
        if (! $config || ! $config->payment_enabled) {
            throw new \Exception('Payment module is not enabled for this village.');
        }

        $types = match (true) {
            is_array($paymentTypeIds) => PaymentType::whereIn('id', $paymentTypeIds)
                ->where('village_id', $villageId)->get(),
            is_int($paymentTypeIds) || is_string($paymentTypeIds) => PaymentType::where('id', $paymentTypeIds)
                ->where('village_id', $villageId)->get(),
            default => $this->getActivePaymentTypes($villageId),
        };

        if ($types->isEmpty()) {
            throw new \Exception('No active payment types found for this village.');
        }

        $households = Household::where('village_id', $villageId)
            ->where('status', 'active')
            ->where('approval_status', 'approved')
            ->get();

        $created = 0;
        $skipped = 0;
        $billedTotals = [];
        $billedDueDates = [];

        foreach ($households as $household) {
            foreach ($types as $type) {
                $outstandingBilling = Billing::where('household_id', $household->id)
                    ->where('payment_type_id', $type->id)
                    ->whereIn('status', ['pending', 'overdue', 'partially_paid'])
                    ->whereNull('deleted_at')
                    ->latest('id')
                    ->first();

                if ($outstandingBilling && $outstandingBilling->billing_period === $billingPeriod) {
                    $skipped++;

                    continue;
                }

                $customAmount = HouseholdPaymentAmount::where('household_id', $household->id)
                    ->where('payment_type_id', $type->id)
                    ->where('village_id', $villageId)
                    ->value('custom_amount');

                $amount = $customAmount !== null ? (float) $customAmount : (float) $type->default_amount;
                $dueDate = $this->calculateDueDate($billingPeriod, $config, $type->frequency);

                if ($outstandingBilling) {
                    $outstandingBilling->update([
                        'billing_number' => Billing::generateBillingNumber($config->billing_prefix),
                        'billing_period' => $billingPeriod,
                        'due_date' => $dueDate,
                        'amount' => (float) $outstandingBilling->amount + $amount,
                        'total_amount' => (float) $outstandingBilling->total_amount + $amount,
                        'status' => \Illuminate\Support\Carbon::parse($dueDate)->lt(now()->startOfDay()) ? 'overdue' : 'pending',
                    ]);
                    $created++;
                } else {
                    Billing::create([
                        'billing_number' => Billing::generateBillingNumber($config->billing_prefix),
                        'household_id' => $household->id,
                        'village_id' => $villageId,
                        'payment_type_id' => $type->id,
                        'billing_period' => $billingPeriod,
                        'due_date' => $dueDate,
                        'amount' => $amount,
                        'penalty' => 0,
                        'discount' => 0,
                        'total_amount' => $amount,
                        'status' => 'pending',
                        'created_by' => auth()->id(),
                    ]);
                    $created++;
                }

                $billedTotals[$household->id] = ($billedTotals[$household->id] ?? 0) + $amount;
                $billedDueDates[$household->id] = isset($billedDueDates[$household->id])
                    ? min($billedDueDates[$household->id], $dueDate)
                    : $dueDate;
            }
        }

        AuditLogService::log(
            'Generate Billings',
            'Payment Management',
            "Generated {$created} billings for village {$villageId} (period: {$billingPeriod})"
        );

        if ($billedTotals) {
            $this->notifyBilledHouseholds(
                $households->whereIn('id', array_keys($billedTotals)),
                $billingPeriod,
                $billedTotals,
                $billedDueDates
            );
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * Tell the resident accounts of every household that just got billed.
     */
    private function notifyBilledHouseholds($households, string $billingPeriod, array $billedTotals, array $billedDueDates): void
    {
        $notifications = app(NotificationService::class);

        foreach ($households as $household) {
            $notifications->notifyHouseholdResidents(
                [$household->id],
                'billing',
                'New Bill Billed',
                "New bill billed to {$household->notification_label} for {$billingPeriod}: ₱"
                    .number_format($billedTotals[$household->id], 2)
                    .', due '.Carbon::parse($billedDueDates[$household->id])->format('M d, Y').'.',
                route('billings.index')
            );
        }
    }

    public function recordPayment(array $data): HouseholdPayment
    {
        return DB::transaction(function () use ($data) {
            $billing = Billing::findOrFail($data['billing_id']);

            $config = $this->getVillageSetting($billing->village_id);
            $receiptPrefix = $config?->receipt_prefix ?? 'RCPT';

            $paymentAmount = (float) ($data['amount'] ?? 0);
            $discount = 0.0;

            if ($paymentAmount <= 0) {
                throw new \Exception('Payment amount must be greater than zero.');
            }

            $existingTotalPaid = (float) $billing->payments()->sum('total_paid');
            $remainingBalance = (float) $billing->total_amount - $existingTotalPaid;

            if ($remainingBalance <= 0) {
                throw new \Exception('This billing is already fully paid. No further payments can be recorded.');
            }

            $netAmount = $paymentAmount;
            if ($netAmount <= 0) {
                throw new \Exception('Payment amount must be greater than zero.');
            }

            if ($netAmount > $remainingBalance + 0.01) {
                throw new \Exception(
                    'Payment amount of ₱'.number_format($netAmount, 2).
                    ' exceeds the remaining balance of ₱'.number_format($remainingBalance, 2).'.'
                );
            }

            $totalPaid = $netAmount;

            $payment = HouseholdPayment::create([
                'receipt_number' => HouseholdPayment::generateReceiptNumber($receiptPrefix),
                'billing_id' => $billing->id,
                'village_id' => $billing->village_id,
                'household_id' => $billing->household_id,
                'payment_type_id' => $billing->payment_type_id,
                'amount' => $paymentAmount,
                'penalty' => 0,
                'discount' => $discount,
                'total_paid' => $totalPaid,
                'billing_number_snapshot' => $billing->billing_number,
                'billing_amount_snapshot' => $billing->total_amount,
                'billing_period_snapshot' => $billing->billing_period,
                'billing_balance_snapshot' => max(0, (float) $billing->total_amount - $existingTotalPaid - $totalPaid),
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                'due_date' => $billing->due_date,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'reference_number' => $data['reference_number'] ?? null,
                'payment_status' => $existingTotalPaid + $netAmount >= (float) $billing->total_amount - 0.01 ? 'paid' : 'partially_paid',
                'received_by' => auth()->id(),
                'remarks' => $data['remarks'] ?? null,
            ]);

            PaymentReceipt::create([
                'receipt_number' => $payment->receipt_number,
                'payment_id' => $payment->id,
                'billing_id' => $billing->id,
                'household_id' => $billing->household_id,
                'village_id' => $billing->village_id,
                'receipt_type' => 'payment',
                'amount_paid' => $totalPaid,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'received_from' => $billing->household?->household_head_name ?? 'Household',
                'received_by' => auth()->id(),
                'receipt_date' => now()->toDateString(),
                'notes' => $data['remarks'] ?? null,
            ]);

            $this->updateBillingStatus($billing);

            AuditLogService::log(
                'Record Payment',
                'Payment Management',
                "Recorded payment {$payment->receipt_number} (₱".number_format($totalPaid, 2).") for billing {$billing->billing_number}"
            );

            app(NotificationService::class)->notifyHouseholdResidents(
                [$billing->household_id],
                'payment',
                'Payment Recorded',
                "A payment of ₱".number_format($totalPaid, 2)." ({$payment->receipt_number}) was recorded for {$billing->billing_number}.",
                route('household-payments.show', $billing->household_id)
            );

            return $payment;
        });
    }

    public function recordPenalty(Billing $billing, float $amount, ?string $reason = null): PaymentPenalty
    {
        return DB::transaction(function () use ($billing, $amount, $reason) {
            $penalty = PaymentPenalty::create([
                'billing_id' => $billing->id,
                'amount' => $amount,
                'reason' => $reason,
                'imposed_date' => now()->toDateString(),
                'imposed_by' => auth()->id(),
                'status' => 'active',
            ]);

            $billing->penalty = (float) $billing->penalty + $amount;
            $billing->total_amount = (float) $billing->amount + (float) $billing->penalty - (float) $billing->discount;
            $billing->save();

            AuditLogService::log(
                'Record Penalty',
                'Payment Management',
                'Penalty of ₱'.number_format($amount, 2)." imposed on billing {$billing->billing_number}"
            );

            return $penalty;
        });
    }

    public function updateOverdueBillings(): int
    {
        $count = 0;
        $configs = PaymentConfiguration::where('payment_enabled', true)->get();

        foreach ($configs as $config) {
            $gracePeriod = $config->grace_period_days;
            $cutoffDate = now()->subDays($gracePeriod)->toDateString();

            $overdueBillings = Billing::where('village_id', $config->village_id)
                ->whereIn('status', ['pending', 'partially_paid'])
                ->where('due_date', '<', now()->toDateString())
                ->get();

            foreach ($overdueBillings as $billing) {
                $billing->status = 'overdue';
                $billing->save();
                $count++;
            }
        }

        return $count;
    }

    public function getVillageCollections(int $villageId, ?string $month = null, ?string $year = null): array
    {
        $billings = Billing::where('village_id', $villageId);

        if ($month && $year) {
            $billings->whereRaw(SqlHelper::dateFormat('billing_period', '%m').' = ?', [$month])
                ->whereRaw(SqlHelper::dateFormat('billing_period', '%Y').' = ?', [$year]);
        } elseif ($year) {
            $billings->whereRaw(SqlHelper::dateFormat('billing_period', '%Y').' = ?', [$year]);
        }

        $payments = HouseholdPayment::where('village_id', $villageId);

        return [
            'total_billed' => (float) $billings->clone()->sum('total_amount'),
            'total_collected' => (float) $payments->clone()->sum('total_paid'),
            'total_outstanding' => (float) $billings->clone()->whereIn('status', ['pending', 'overdue', 'partially_paid'])->sum(DB::raw('total_amount - penalty')),
            'total_overdue' => (float) $billings->clone()->where('status', 'overdue')->sum('total_amount'),
            'total_penalties' => (float) $billings->clone()->sum('penalty'),
            'paid_count' => $billings->clone()->where('status', 'paid')->count(),
            'pending_count' => $billings->clone()->where('status', 'pending')->count(),
            'overdue_count' => $billings->clone()->where('status', 'overdue')->count(),
            'cancelled_count' => $billings->clone()->where('status', 'cancelled')->count(),
            'total_households_paid' => $billings->clone()->where('status', 'paid')->distinct('household_id')->count('household_id'),
            'total_households_unpaid' => $billings->clone()->whereIn('status', ['pending', 'overdue'])->distinct('household_id')->count('household_id'),
        ];
    }

    public function getSystemWideStats(): array
    {
        $billings = Billing::query();
        $payments = HouseholdPayment::query();

        return [
            'total_billed' => (float) $billings->clone()->sum('total_amount'),
            'total_collected' => (float) $payments->clone()->sum('total_paid'),
            'total_outstanding' => (float) $billings->clone()->whereIn('status', ['pending', 'overdue', 'partially_paid'])->sum(DB::raw('total_amount - penalty')),
            'total_overdue' => (float) $billings->clone()->where('status', 'overdue')->sum('total_amount'),
            'total_penalties' => (float) $billings->clone()->sum('penalty'),
            'total_payments' => $payments->clone()->count(),
            'villages_with_payments' => $billings->clone()->distinct('village_id')->count('village_id'),
        ];
    }

    public function getHouseholdBillings(int $householdId)
    {
        return Billing::where('household_id', $householdId)
            ->with(['paymentType', 'village', 'creator', 'payments.receivedByUser'])
            ->latest()
            ->get();
    }

    public function getHouseholdPayments(int $householdId)
    {
        return HouseholdPayment::where('household_id', $householdId)
            ->with(['billing', 'paymentType', 'village', 'receivedByUser'])
            ->latest()
            ->get();
    }

    public function getHouseholdBalance(int $householdId): array
    {
        $billings = Billing::where('household_id', $householdId);
        $payments = HouseholdPayment::where('household_id', $householdId);

        $totalDue = (float) $billings->clone()->sum('total_amount');

        $totalPaid = (float) $payments->clone()->sum('total_paid');

        $penalties = (float) $billings->clone()->sum('penalty');

        $outstanding = max(0, $totalDue - $totalPaid);

        return [
            'outstanding' => $outstanding,
            'paid' => $totalPaid,
            'penalties' => $penalties,
        ];
    }

    public function getMonthlyCollections(int $villageId): array
    {
        $currentMonth = now()->format('Y-m');

        $billings = Billing::where('village_id', $villageId)
            ->where('billing_period', $currentMonth);

        $payments = HouseholdPayment::where('village_id', $villageId)
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year);

        return [
            'monthly_billed' => (float) $billings->clone()->sum('total_amount'),
            'monthly_collected' => (float) $payments->clone()->sum('total_paid'),
            'monthly_outstanding' => (float) $billings->clone()->whereIn('status', ['pending', 'overdue'])->sum(DB::raw('total_amount - penalty')),
            'monthly_overdue' => (float) $billings->clone()->where('status', 'overdue')->sum('total_amount'),
        ];
    }

    /**
     * Collections made during the current calendar month for monthly dues only -
     * the payment types the treasurer configures with a monthly frequency and
     * bills to the village households.
     */
    public function getMonthlyDuesCollections(int $villageId): array
    {
        $currentPeriod = now()->format('Y-m');
        $previousPeriod = now()->subMonth()->format('Y-m');

        $monthlyTypeIds = PaymentType::where('village_id', $villageId)
            ->where('frequency', 'monthly')
            ->pluck('id')
            ->all();

        $collectedSummary = HouseholdPayment::where('village_id', $villageId)
            ->whereIn('payment_type_id', $monthlyTypeIds)
            ->where('payment_status', 'paid')
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->selectRaw(
                'COALESCE(SUM(total_paid), 0) as collected, COUNT(*) as payment_count, COUNT(DISTINCT household_id) as households_paid'
            )
            ->first();

        $previousCollected = (float) HouseholdPayment::where('village_id', $villageId)
            ->whereIn('payment_type_id', $monthlyTypeIds)
            ->where('payment_status', 'paid')
            ->whereMonth('payment_date', now()->subMonth()->month)
            ->whereYear('payment_date', now()->subMonth()->year)
            ->sum('total_paid');

        $billingSummary = Billing::where('village_id', $villageId)
            ->whereIn('payment_type_id', $monthlyTypeIds)
            ->where('billing_period', $currentPeriod)
            ->selectRaw(
                "COALESCE(SUM(total_amount), 0) as billed, ".
                "COALESCE(SUM(CASE WHEN status IN ('pending', 'overdue', 'partially_paid') ".
                'THEN total_amount - penalty ELSE 0 END), 0) as outstanding'
            )
            ->first();

        $collected = (float) ($collectedSummary->collected ?? 0);

        return [
            'label' => now()->format('F Y'),
            'period' => $currentPeriod,
            'previous_period' => $previousPeriod,
            'collected' => $collected,
            'billed' => (float) ($billingSummary->billed ?? 0),
            'outstanding' => (float) ($billingSummary->outstanding ?? 0),
            'payment_count' => (int) ($collectedSummary->payment_count ?? 0),
            'households_paid' => (int) ($collectedSummary->households_paid ?? 0),
            'change' => $previousCollected > 0 ? round(($collected / $previousCollected) * 100 - 100, 1) : 0,
        ];
    }

    private function updateBillingStatus(Billing $billing): void
    {
        $totalPaid = (float) $billing->payments()->sum('total_paid');

        $totalDue = (float) $billing->total_amount;

        if ($totalPaid >= $totalDue - 0.01) {
            $billing->status = 'paid';
        } else {
            $billing->status = $billing->due_date->lt(now()->startOfDay()) ? 'overdue' : 'pending';
        }

        $billing->save();
    }

    private function calculateDueDate(string $billingPeriod, PaymentConfiguration $config, string $frequency = 'monthly'): string
    {
        $parts = explode('-', $billingPeriod);
        $year = (int) $parts[0];
        $month = isset($parts[1]) ? (int) $parts[1] : 1;

        if ($frequency === 'annual') {
            $dueMonth = $config->annual_due_month ?: 1;
            $dueDay = $config->annual_due_day ?: 15;
            $dueYear = $year;
            if ($month > $dueMonth || ($month === $dueMonth && now()->day > $dueDay)) {
                $dueYear = $year + 1;
            }
            $day = min($dueDay, Carbon::create($dueYear, $dueMonth, 1)->daysInMonth);

            return Carbon::create($dueYear, $dueMonth, $day)->toDateString();
        }

        $dueDay = $config->monthly_due_day ?: 15;

        $nextMonth = $month + 1;
        $nextYear = $year;
        if ($nextMonth > 12) {
            $nextMonth = 1;
            $nextYear = $year + 1;
        }

        $day = min($dueDay, Carbon::create($nextYear, $nextMonth, 1)->daysInMonth);

        return Carbon::create($nextYear, $nextMonth, $day)->toDateString();
    }
}
