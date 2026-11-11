<?php

namespace App\Http\Requests\EventRequest;

use App\Models\Village;
use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'village_id' => ['required', function ($attribute, $value, $fail) {
                // "all" = All Villages, reserved for super admins; stored as null.
                if ($value === 'all') {
                    if (($this->user()?->role?->role_slug) !== 'super_admin') {
                        $fail('Only super admins can target all villages.');
                    }

                    return;
                }

                if (! is_numeric($value) || ! Village::whereKey($value)->exists()) {
                    $fail('The selected village is invalid.');
                }
            }],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'venue' => ['nullable', 'string', 'max:255'],
            'event_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after_or_equal:start_time'],
            'max_participants' => ['nullable', 'integer', 'min:1'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'village_id.required' => 'Please select a village.',
            'title.required' => 'Title is required.',
            'category.required' => 'Category is required.',
            'event_date.required' => 'Event date is required.',
            'end_time.after_or_equal' => 'End time must be on or after start time.',
        ];
    }
}
