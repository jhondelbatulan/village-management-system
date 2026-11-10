<?php

namespace App\Http\Requests\ResidentRequest;

use App\Rules\ProperName;
use App\Rules\SafeText;
use App\Support\TextNormalizer;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHouseholdMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        foreach (['first_name', 'last_name'] as $field) {
            $value = $this->input($field);

            if (is_string($value)) {
                $merge[$field] = TextNormalizer::titleCase($value);
            }
        }

        $middleName = $this->input('middle_name');

        if (is_string($middleName)) {
            $middleName = trim((string) preg_replace('/\s+/u', ' ', $middleName));

            if (preg_match('/^\p{L}$/u', $middleName)) {
                $middleName = mb_strtoupper($middleName, 'UTF-8');
            }

            $merge['middle_name'] = $middleName === '' ? null : $middleName;
        }

        $suffix = $this->input('suffix');

        if (is_string($suffix)) {
            $merge['suffix'] = trim($suffix) === '' ? null : trim($suffix);
        }

        foreach (['contact_number', 'email', 'occupation', 'relationship_to_head'] as $field) {
            $value = $this->input($field);

            if (is_string($value)) {
                $merge[$field] = trim($value) === '' ? null : trim($value);
            }
        }

        // Keep the stored age in sync whenever the birthdate changes.
        $birthdate = $this->input('birthdate');

        if (is_string($birthdate) && strtotime($birthdate) !== false) {
            $computedAge = Carbon::parse($birthdate)->age;
            $merge['age'] = $computedAge >= 0 ? $computedAge : null;
        }

        $this->merge($merge);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255', new ProperName],
            'middle_name' => ['nullable', 'string', 'max:255', new ProperName],
            'last_name' => ['required', 'string', 'max:255', new ProperName],
            'suffix' => ['nullable', 'string', 'max:50', new SafeText],
            'gender' => ['required', 'in:Male,Female,Other'],
            'birthdate' => ['required', 'date', 'before:today'],
            'civil_status' => ['required', 'in:Single,Married,Widowed,Separated,Divorced'],
            'contact_number' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:255', new SafeText],
            'relationship_to_head' => ['nullable', 'string', 'max:100', new SafeText],
            'age' => ['nullable', 'integer', 'min:0', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'gender.required' => 'Please select a gender.',
            'gender.in' => 'The selected gender is invalid.',
            'birthdate.required' => 'Birthdate is required.',
            'birthdate.before' => 'Birthdate must be in the past.',
            'civil_status.required' => 'Please select a civil status.',
            'civil_status.in' => 'The selected civil status is invalid.',
            'contact_number.regex' => 'Contact number must be a valid phone number (7–20 digits, may include +, -, spaces or parentheses).',
            'email.email' => 'Please enter a valid email address.',
        ];
    }
}
