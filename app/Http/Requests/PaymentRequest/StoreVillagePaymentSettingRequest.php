<?php

namespace App\Http\Requests\PaymentRequest;

use App\Models\Village;
use Illuminate\Foundation\Http\FormRequest;

class StoreVillagePaymentSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        $role = $user->role?->role_slug;

        if ($role === 'super_admin') {
            return true;
        }

        if ($role === 'treasurer') {
            $village = $this->route('village');

            return $village instanceof Village
                && $user->village_id !== null
                && (int) $user->village_id === (int) $village->id;
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'payment_enabled' => ['nullable'],
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
            'grace_period_days.required' => 'Grace period is required.',
            'billing_prefix.required' => 'Billing prefix is required.',
            'receipt_prefix.required' => 'Receipt prefix is required.',
            'permit_prefix.required' => 'Permit prefix is required.',
        ];
    }
}
