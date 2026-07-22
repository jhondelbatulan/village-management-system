<?php

namespace App\Http\Requests\VillageRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVillageOfficerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'position' => ['required', 'string', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'term_start' => ['nullable', 'date'],
            'term_end' => ['nullable', 'date', 'after_or_equal:term_start'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
