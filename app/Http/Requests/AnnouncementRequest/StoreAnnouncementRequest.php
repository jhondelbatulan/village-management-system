<?php

namespace App\Http\Requests\AnnouncementRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'village_id' => ['required', 'exists:villages,id'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:5120'],
            'start_date' => ['nullable', 'date'],
            'expiration_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:draft,published,archived'],
            'is_pinned' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'village_id.required' => 'Please select a village.',
            'title.required' => 'Title is required.',
            'category.required' => 'Category is required.',
            'expiration_date.after_or_equal' => 'Expiration date must be on or after the start date.',
        ];
    }
}
