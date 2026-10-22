<?php

namespace App\Http\Requests\HouseholdRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHouseholdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        foreach (['block_number', 'lot_number'] as $field) {
            $value = $this->input($field);

            if (is_string($value)) {
                $normalized = ltrim(trim($value), '0');
                $merge[$field] = $normalized === '' ? '0' : $normalized;
            }
        }

        $this->merge($merge);
    }

    public function rules(): array
    {
        $household = $this->route('household');

        return [
            'block_number' => ['required', 'string', 'max:50'],
            'lot_number' => ['required', 'string', 'max:50'],
            'household_head_name' => ['nullable', 'string', 'max:255'],
            'purok' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'household_type' => ['nullable', 'string', 'in:1,2,3,4,5'],
            'house_ownership' => ['nullable', 'string', 'in:Owned,Leased'],
            'house_status' => ['required', 'in:concrete,wood_timber,steel,brick_blocks,stone,mixed,other'],
            'registration_date' => ['nullable', 'date'],
            'status' => ['required', 'in:active,inactive,relocated'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $household = $this->route('household');
            $blockNumber = $this->input('block_number');
            $lotNumber = $this->input('lot_number');

            if ($household && $blockNumber && $lotNumber) {
                $exists = \App\Models\Household::withTrashed()
                    ->where('village_id', $household->village_id)
                    ->where('block_number', $blockNumber)
                    ->where('lot_number', $lotNumber)
                    ->where('id', '!=', $household->id)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('block_number', 'This Block and Lot combination already exists in the selected village.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'block_number.required' => 'Block number is required.',
            'lot_number.required' => 'Lot number is required.',
            'address.required' => 'Address is required.',
            'house_status.required' => 'House material is required.',
            'house_status.in' => 'The selected house material is invalid.',
        ];
    }
}
