<?php

namespace App\Http\Requests\VillageRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreVillageDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Document title is required.',
            'file.required' => 'Please select a file to upload.',
            'file.mimes' => 'Only PDF, Word, JPG, JPEG, and PNG files are allowed.',
            'file.max' => 'File size must not exceed 10MB.',
        ];
    }
}
