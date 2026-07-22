<?php

namespace App\Http\Requests\HouseholdRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHouseholdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
            'household_type' => ['nullable', 'string', 'max:100'],
            'house_ownership' => ['nullable', 'string', 'max:100'],
            'house_status' => ['required', 'in:owned,rented,leased,inherited,other'],
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
                $exists = \App\Models\Household::where('village_id', $household->village_id)
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
            'house_status.required' => 'House status is required.',
        ];
    }
}
