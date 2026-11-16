<?php

namespace App\Http\Requests\ResidentRequest;

use App\Rules\ProperName;
use App\Rules\SafeText;
use App\Support\TextNormalizer;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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

        foreach ([
            'is_pwd', 'is_solo_parent', 'is_senior_citizen', 'is_youth', 'is_student', 'is_ofw', 'is_infant',
            'is_pagibig_member', 'is_philhealth_member', 'is_sss_member', 'is_gsis_member',
            'is_4ps_member', 'is_senior_pensioner',
        ] as $field) {
            $merge[$field] = $this->boolean($field);
        }

        $this->merge($merge);
    }

    public function rules(): array
    {
        $resident = $this->route('resident');
        $educationOptions = ['No Formal Education','Preschool','Elementary Level','Elementary Graduate','High School Level','High School Graduate','Vocational/Technical','Senior High Graduate','College Level','College Graduate','Post Graduate'];
        $incomeOptions = ['Below ₱5,000','₱5,000 - ₱10,000','₱10,001 - ₱20,000','₱20,001 - ₱40,000','₱40,001 - ₱60,000','₱60,001 - ₱100,000','Above ₱100,000'];
        $employmentOptions = ['Employed','Self-Employed','Unemployed','Retired','Student','Homemaker','Disabled'];

        return [
            'first_name' => ['required', 'string', 'max:255', new ProperName],
            'middle_name' => ['nullable', 'string', 'max:1', 'regex:/^\p{Lu}$/u'],
            'last_name' => ['required', 'string', 'max:255', new ProperName],
            'suffix' => ['nullable', 'string', 'max:50', new SafeText],
            'gender' => ['required', 'in:Male,Female,Other'],
            'birthdate' => ['required', 'date', 'before:today'],
            'civil_status' => ['required', 'in:Single,Married,Widowed,Separated,Divorced'],
            'contact_number' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]{7,20}$/'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:255', new SafeText],
            'relationship_to_head' => ['nullable', 'string', 'max:100', new SafeText],
            'age' => ['nullable', 'integer', 'min:0', 'max:150'],
            'educational_attainment' => ['nullable', 'string', 'max:255', Rule::in(array_merge($educationOptions, [$resident?->educational_attainment]))],
            'monthly_income_range' => ['nullable', 'string', 'max:100', Rule::in(array_merge($incomeOptions, [$resident?->monthly_income_range]))],
            'employment_status' => ['nullable', 'string', 'max:50', Rule::in(array_merge($employmentOptions, [$resident?->employment_status]))],
            'is_pwd' => ['nullable', 'boolean'],
            'pwd_type' => ['nullable', 'string', 'max:255', new SafeText],
            'is_solo_parent' => ['nullable', 'boolean'],
            'is_senior_citizen' => ['nullable', 'boolean'],
            'is_youth' => ['nullable', 'boolean'],
            'is_student' => ['nullable', 'boolean'],
            'is_ofw' => ['nullable', 'boolean'],
            'is_infant' => ['nullable', 'boolean'],
            'is_pagibig_member' => ['nullable', 'boolean'],
            'is_philhealth_member' => ['nullable', 'boolean'],
            'is_sss_member' => ['nullable', 'boolean'],
            'is_gsis_member' => ['nullable', 'boolean'],
            'is_4ps_member' => ['nullable', 'boolean'],
            'is_senior_pensioner' => ['nullable', 'boolean'],
            'skills' => ['nullable', 'string', 'max:1000', new SafeText],
            'blood_type' => ['nullable', 'string', 'max:10', 'regex:/^(A|B|AB|O)[+-]$|^Unknown$/i'],
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
            'contact_number.regex' => 'Contact number must contain only digits (7–20 digits).',
            'email.email' => 'Please enter a valid email address.',
        ];
    }
}
