<?php

namespace App\Http\Requests\HouseholdRequest;

use App\Support\TextNormalizer;
use Illuminate\Foundation\Http\FormRequest;

class StoreHouseholdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        $headName = $this->input('household_head_name');

        if (is_string($headName)) {
            $merge['household_head_name'] = TextNormalizer::titleCase($headName);
        }

        // The registration date is not editable: it is always the date the household is registered.
        $merge['registration_date'] = now()->toDateString();

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
        return [
            'village_id' => ['required', 'exists:villages,id'],
            'block_number' => ['required', 'string', 'max:50', 'regex:/^[0-9]+$/'],
            'lot_number' => ['required', 'string', 'max:50', 'regex:/^[0-9]+$/'],
            'household_head_name' => ['required', 'string', 'max:255', 'regex:/^[\p{L}]+(?: [\p{L}]+)*$/u'],
            'purok' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'household_type' => ['nullable', 'string', 'in:1,2,3,4,5'],
            'house_ownership' => ['nullable', 'string', 'in:Owned,Leased'],
            'house_status' => ['required', 'in:concrete,wood_timber,steel,brick_blocks,stone,mixed,other'],
            'registration_date' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive,relocated'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $villageId = $this->input('village_id');
            $blockNumber = $this->input('block_number');
            $lotNumber = $this->input('lot_number');

            if ($villageId && $blockNumber && $lotNumber) {
                $exists = \App\Models\Household::withTrashed()
                    ->where('village_id', $villageId)
                    ->where('block_number', $blockNumber)
                    ->where('lot_number', $lotNumber)
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
            'village_id.required' => 'Please select a village.',
            'village_id.exists' => 'The selected village is invalid.',
            'block_number.required' => 'Block number is required.',
            'block_number.regex' => 'Block number must only contain numbers.',
            'lot_number.required' => 'Lot number is required.',
            'lot_number.regex' => 'Lot number must only contain numbers.',
            'household_head_name.required' => 'Household head name is required.',
            'household_head_name.regex' => 'Household head name must only contain letters — no numbers or special characters.',
            'address.required' => 'Address is required.',
            'house_status.required' => 'House material is required.',
            'house_status.in' => 'The selected house material is invalid.',
            'registration_date.date' => 'Registration date must be a valid date.',
            'status.required' => 'Status is required.',
        ];
    }
}
