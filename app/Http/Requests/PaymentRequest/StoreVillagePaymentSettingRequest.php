<?php

namespace App\Http\Requests\PaymentRequest;

use App\Services\PaymentPermissionService;
use Illuminate\Foundation\Http\FormRequest;

class StoreVillagePaymentSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        if (!$user || $user->role?->role_slug !== 'super_admin') {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'payment_enabled' => ['nullable'],
            'payment_manager_role' => ['nullable', 'in:treasurer,super_admin'],
            'penalty_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'grace_period_days' => ['required', 'integer', 'min:0'],
            'billing_prefix' => ['required', 'string', 'max:10'],
            'receipt_prefix' => ['required', 'string', 'max:10'],
            'permit_prefix' => ['required', 'string', 'max:10'],
            'payment_notes' => ['nullable', 'string', 'max:500'],
            'monthly_due_day' => ['nullable', 'integer', 'min:1', 'max:28'],
            'annual_due_month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'annual_due_day' => ['nullable', 'integer', 'min:1', 'max:28'],
        ];
    }

    public function messages(): array
    {
        return [
            'penalty_rate.required' => 'Penalty rate is required.',
            'grace_period_days.required' => 'Grace period is required.',
            'billing_prefix.required' => 'Billing prefix is required.',
            'receipt_prefix.required' => 'Receipt prefix is required.',
            'permit_prefix.required' => 'Permit prefix is required.',
        ];
    }
}
