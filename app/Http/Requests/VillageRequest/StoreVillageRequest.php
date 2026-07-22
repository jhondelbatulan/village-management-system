<?php

namespace App\Http\Requests\VillageRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreVillageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'village_name' => ['required', 'string', 'max:255', 'unique:villages,village_name'],
            'village_code' => ['required', 'string', 'max:20', 'unique:villages,village_code'],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:500'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'total_area' => ['nullable', 'numeric', 'min:0'],
            'established_date' => ['nullable', 'date'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg,webp', 'max:2048'],
            'cover_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg,webp', 'max:4096'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'village_name.required' => 'Village name is required.',
            'village_name.unique' => 'A village with this name already exists.',
            'village_code.required' => 'Village code is required.',
            'village_code.unique' => 'This village code is already taken.',
            'email.email' => 'Please enter a valid email address.',
            'logo.image' => 'Logo must be an image file.',
            'cover_photo.image' => 'Cover photo must be an image file.',
        ];
    }
}
