<?php

namespace App\Services;

use App\Models\Billing;
use App\Models\Household;
use App\Models\HouseholdPayment;
use App\Models\PaymentConfiguration;
use App\Models\User;

class PaymentPermissionService
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function canViewPayments(User $user, ?int $villageId = null): bool
    {
        return in_array($user->role?->role_slug, ['super_admin', 'admin', 'treasurer', 'secretary']);
    }

    public function canRecordPayment(User $user, ?int $villageId = null): bool
    {
        if ($villageId) {
            return $this->isPaymentManagerFor($user, $villageId);
        }

        return !empty($this->getPaymentManagerVillageIds($user));
    }

    public function canGenerateBillings(User $user, ?int $villageId = null): bool
    {
        if ($villageId) {
            return $this->isPaymentManagerFor($user, $villageId);
        }

        return !empty($this->getPaymentManagerVillageIds($user));
    }

    public function canManagePaymentConfig(User $user, ?int $villageId = null): bool
    {
        if ($user->role?->role_slug === 'treasurer') {
            return $villageId !== null && $user->village_id === $villageId;
        }

        return false;
    }

    public function canViewPaymentConfig(User $user, ?int $villageId = null): bool
    {
        $role = $user->role?->role_slug;

        if ($role === 'super_admin') {
            return true;
        }

        if ($role === 'treasurer') {
            return $villageId !== null && $user->village_id === $villageId;
        }

        return false;
    }

    public function isPaymentManagerFor(User $user, ?int $villageId): bool
    {
        if (!$villageId) {
            return false;
        }

        if ($user->role?->role_slug === 'treasurer') {
            if ($user->village_id !== $villageId) {
                return false;
            }
            $config = PaymentConfiguration::where('village_id', $villageId)->first();
            return $config && $config->payment_manager_role === 'treasurer';
        }

        return false;
    }

    public function getViewableVillageIds(User $user): array
    {
        $role = $user->role?->role_slug;

        if ($role === 'super_admin') {
            return PaymentConfiguration::where('payment_enabled', true)
                ->pluck('village_id')
                ->toArray();
        }

        if (in_array($role, ['admin', 'treasurer', 'secretary'])) {
            return $user->village_id ? [$user->village_id] : [];
        }

        return [];
    }

    public function canRecordPaymentForBilling(User $user, Billing $billing): bool
    {
        return $this->canRecordPayment($user, $billing->village_id);
    }

    public function canViewBilling(User $user, Billing $billing): bool
    {
        $role = $user->role?->role_slug;

        if (in_array($role, ['super_admin', 'admin', 'treasurer', 'secretary'])) {
            return true;
        }

        if ($role === 'resident') {
            $household = $billing->household;
            return $household && $household->id === $user->household_id;
        }

        return false;
    }

    public function canViewPayment(User $user, HouseholdPayment $payment): bool
    {
        $role = $user->role?->role_slug;

        if (in_array($role, ['super_admin', 'admin', 'treasurer', 'secretary'])) {
            return true;
        }

        if ($role === 'resident') {
            return $payment->household_id === $user->household_id;
        }

        return false;
    }

    public function canPrintReceipt(User $user, HouseholdPayment $payment): bool
    {
        return $this->canViewPayment($user, $payment);
    }

    public function isReadOnlyVillage(User $user, int $villageId): bool
    {
        $role = $user->role?->role_slug;

        if ($role === 'super_admin') {
            return true;
        }

        if ($role === 'admin') {
            return true;
        }

        if ($role === 'treasurer') {
            return $user->village_id !== $villageId;
        }

        return true;
    }

    public function authorizeViewPaymentConfig(User $user, ?int $villageId = null): void
    {
        if (!$this->canViewPaymentConfig($user, $villageId)) {
            abort(403, 'You are not authorized to view payment configuration.');
        }
    }

    public function authorizeRecordPayment(User $user, int $villageId): void
    {
        if (!$this->canRecordPayment($user, $villageId)) {
            abort(403, 'You are not authorized to record payments for this village.');
        }

        $config = PaymentConfiguration::where('village_id', $villageId)->first();
        if (!$config || !$config->payment_enabled) {
            abort(403, 'Payment module is not enabled for this village.');
        }
    }

    public function authorizeGenerateBillings(User $user, int $villageId): void
    {
        if (!$this->canGenerateBillings($user, $villageId)) {
            abort(403, 'You are not authorized to generate billings for this village.');
        }

        $config = PaymentConfiguration::where('village_id', $villageId)->first();
        if (!$config || !$config->payment_enabled) {
            abort(403, 'Payment module is not enabled for this village.');
        }
    }

    public function authorizePaymentConfig(User $user, ?int $villageId = null): void
    {
        if (!$this->canManagePaymentConfig($user, $villageId)) {
            abort(403, 'You are not authorized to manage payment configuration.');
        }
    }

    public function getPaymentManagerVillageIds(User $user): array
    {
        if ($user->role?->role_slug === 'treasurer') {
            if (!$user->village_id) {
                return [];
            }
            $config = PaymentConfiguration::where('village_id', $user->village_id)
                ->where('payment_manager_role', 'treasurer')
                ->first();
            return $config ? [$user->village_id] : [];
        }

        return [];
    }
}
