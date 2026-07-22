<?php

namespace App\Http\Requests\ResidentRequest;

use Illuminate\Foundation\Http\FormRequest;

class ApproveResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'approval_status' => ['required', 'in:approved,rejected'],
            'remarks' => ['required_if:approval_status,rejected', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'approval_status.required' => 'Please select an approval action.',
            'approval_status.in' => 'Invalid approval action.',
            'remarks.required_if' => 'Remarks are required when rejecting a record.',
        ];
    }
}
