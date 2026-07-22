<?php

namespace App\Http\Requests\VillageRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVillageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $villageId = $this->route('village')?->id;

        return [
            'village_name' => ['required', 'string', 'max:255', 'unique:villages,village_name,' . $villageId],
            'village_code' => ['required', 'string', 'max:20', 'unique:villages,village_code,' . $villageId],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:500'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'total_area' => ['nullable', 'numeric', 'min:0'],
            'established_date' => ['nullable', 'date'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg,webp', 'max:2048'],
            'cover_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg,webp', 'max:4096'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg,webp', 'max:2048'],
            'cover_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg,webp', 'max:4096'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
