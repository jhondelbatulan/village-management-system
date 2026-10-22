<?php

namespace App\Http\Requests\HouseholdRequest;

use App\Rules\SafeText;
use App\Support\TextNormalizer;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RequestMemberRequest extends FormRequest
{
    /**
     * Suffixes that may be stored on a resident.
     */
    public const SUFFIXES = ['Jr.', 'Sr.', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];

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

            $merge['middle_name'] = $middleName;
        }

        $suffix = $this->input('suffix');

        if (is_string($suffix)) {
            $merge['suffix'] = $this->normalizeSuffix($suffix);
        }

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
            'first_name' => ['required', 'string', 'max:255', 'regex:/^[\p{L}]+(?: [\p{L}]+)*$/u'],
            'middle_name' => ['nullable', 'string', 'max:255', 'regex:/^\p{Lu}$/u'],
            'last_name' => ['required', 'string', 'max:255', 'regex:/^[\p{L}]+(?: [\p{L}]+)*$/u'],
            'suffix' => ['nullable', 'string', 'max:10', Rule::in(self::SUFFIXES)],
            'age' => ['nullable', 'integer', 'min:0', 'max:130'],
            'gender' => 'required|in:Male,Female,Other',
            'birthdate' => 'required|date|before:today',
            'civil_status' => 'required|in:Single,Married,Widowed,Separated,Divorced',
            'nationality' => ['nullable', 'string', 'max:100', new SafeText],
            'religion' => ['nullable', 'string', 'max:100', new SafeText],
            'contact_number' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'email' => 'nullable|email|max:255',
            'relationship_to_head' => ['nullable', 'string', 'max:100', new SafeText],
            'voter_status' => 'nullable|boolean',
            'is_household_head' => 'nullable|boolean',

            // Education
            'educational_attainment' => 'nullable|string|max:100',
            'education_level' => 'nullable|string|max:100',
            'school_name' => ['nullable', 'string', 'max:255', new SafeText],
            'grade_level' => ['nullable', 'string', 'max:100', new SafeText],
            'is_scholarship' => 'nullable|boolean',
            'scholarship_type' => ['nullable', 'string', 'max:255', new SafeText],

            // Employment
            'employment_status' => 'nullable|string|max:50',
            'occupation' => ['nullable', 'string', 'max:255', new SafeText],
            'employer_name' => ['nullable', 'string', 'max:255', new SafeText],
            'job_title' => ['nullable', 'string', 'max:255', new SafeText],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:60'],

            // Income
            'income_source' => ['nullable', 'string', 'max:100'],
            'monthly_income' => ['nullable', 'numeric', 'min:0'],
            'monthly_income_range' => ['nullable', 'string', 'max:100'],

            // Special Population
            'is_pwd' => 'nullable|boolean',
            'pwd_type' => ['nullable', 'string', 'max:255', new SafeText],
            'is_solo_parent' => 'nullable|boolean',
            'is_senior_citizen' => 'nullable|boolean',
            'is_youth' => 'nullable|boolean',
            'is_student' => 'nullable|boolean',
            'is_ofw' => 'nullable|boolean',
            'is_infant' => 'nullable|boolean',

            // Government Memberships
            'is_pagibig_member' => 'nullable|boolean',
            'is_philhealth_member' => 'nullable|boolean',
            'is_sss_member' => 'nullable|boolean',
            'is_gsis_member' => 'nullable|boolean',

            // Government Assistance
            'is_4ps_member' => 'nullable|boolean',
            'is_senior_pensioner' => 'nullable|boolean',
            'other_gov_assistance' => ['nullable', 'string', 'max:255', new SafeText],
            'is_tupad_beneficiary' => 'nullable|boolean',
            'is_akap_beneficiary' => 'nullable|boolean',
            'is_educational_assistance' => 'nullable|boolean',
            'is_emergency_assistance' => 'nullable|boolean',
            'is_slp_beneficiary' => 'nullable|boolean',

            // Government IDs
            'is_national_id_holder' => 'nullable|boolean',
            'is_senior_id_holder' => 'nullable|boolean',
            'is_pwd_id_holder' => 'nullable|boolean',
            'is_solo_parent_id_holder' => 'nullable|boolean',

            // Social Categories
            'is_indigenous' => 'nullable|boolean',
            'is_veteran' => 'nullable|boolean',

            // Community Participation
            'is_disaster_volunteer' => 'nullable|boolean',
            'is_village_volunteer' => 'nullable|boolean',

            // Skills & Livelihood
            'skills' => ['nullable', 'string', 'max:1000', new SafeText],
            'primary_skill' => ['nullable', 'string', 'max:255', new SafeText],
            'secondary_skill' => ['nullable', 'string', 'max:255', new SafeText],
            'livelihood_programs' => ['nullable', 'string', 'max:1000', new SafeText],
            'special_qualifications' => ['nullable', 'string', 'max:1000', new SafeText],
            'community_activities' => ['nullable', 'string', 'max:1000', new SafeText],

            // Emergency Contact
            'emergency_contact_name' => ['nullable', 'string', 'max:255', new SafeText],
            'emergency_contact_number' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:100', new SafeText],

            // Additional
            'blood_type' => 'nullable|string|max:10',
            'years_of_residency' => ['nullable', 'integer', 'min:0', 'max:120'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'gender.required' => 'Gender is required.',
            'birthdate.required' => 'Birth date is required.',
            'birthdate.before' => 'Birth date cannot be in the future.',
            'civil_status.required' => 'Civil status is required.',
            'email.email' => 'Please enter a valid email address.',
            'first_name.regex' => 'First name must only contain letters - no numbers or special characters.',
            'last_name.regex' => 'Last name must only contain letters - no numbers or special characters.',
            'middle_name.regex' => 'Middle name must be a single capital letter (e.g., M).',
            'suffix.in' => 'Suffix must only be one of: ' . implode(', ', self::SUFFIXES) . '.',
            'age.integer' => 'Age must be a whole number.',
            'age.min' => 'Age must be 0 or older.',
            'age.max' => 'Age must not exceed 130.',
        ];
    }

    private function normalizeSuffix(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        $key = strtolower(rtrim($value, '.'));

        return match ($key) {
            'jr' => 'Jr.',
            'sr' => 'Sr.',
            'ii' => 'II',
            'iii' => 'III',
            'iv' => 'IV',
            'v' => 'V',
            'vi' => 'VI',
            'vii' => 'VII',
            'viii' => 'VIII',
            'ix' => 'IX',
            'x' => 'X',
            default => $value,
        };
    }
}
