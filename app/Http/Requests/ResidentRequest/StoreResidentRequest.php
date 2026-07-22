<?php

namespace App\Http\Requests\ResidentRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'household_id' => ['required', 'exists:households,id'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:50'],
            'gender' => ['required', 'in:Male,Female,Other'],
            'birthdate' => ['required', 'date', 'before:today'],
            'civil_status' => ['required', 'in:Single,Married,Widowed,Separated,Divorced'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'educational_attainment' => ['nullable', 'string', 'max:255'],
            'religion' => ['nullable', 'string', 'max:100'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'relationship_to_head' => ['nullable', 'string', 'max:100'],
            'voter_status' => ['nullable', 'boolean'],
            'is_household_head' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,inactive,deceased,transferred'],
            // Education
            'education_level' => ['nullable', 'string', 'max:100'],
            'school_name' => ['nullable', 'string', 'max:255'],
            'grade_level' => ['nullable', 'string', 'max:100'],
            'is_scholarship' => ['nullable', 'boolean'],
            'scholarship_type' => ['nullable', 'string', 'max:255'],
            // Employment
            'employment_status' => ['nullable', 'string', 'max:50'],
            'employer_name' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:60'],
            // Income
            'income_source' => ['nullable', 'string', 'max:100'],
            'monthly_income' => ['nullable', 'numeric', 'min:0'],
            'monthly_income_range' => ['nullable', 'string', 'max:100'],
            // Special Population
            'is_pwd' => ['nullable', 'boolean'],
            'pwd_type' => ['nullable', 'string', 'max:255'],
            'is_solo_parent' => ['nullable', 'boolean'],
            'is_senior_citizen' => ['nullable', 'boolean'],
            'is_youth' => ['nullable', 'boolean'],
            'is_student' => ['nullable', 'boolean'],
            'is_ofw' => ['nullable', 'boolean'],
            'is_infant' => ['nullable', 'boolean'],
            // Government Memberships
            'is_pagibig_member' => ['nullable', 'boolean'],
            'is_philhealth_member' => ['nullable', 'boolean'],
            'is_sss_member' => ['nullable', 'boolean'],
            'is_gsis_member' => ['nullable', 'boolean'],
            // Government Assistance
            'is_4ps_member' => ['nullable', 'boolean'],
            'is_senior_pensioner' => ['nullable', 'boolean'],
            'other_gov_assistance' => ['nullable', 'string', 'max:255'],
            // Skills & Livelihood
            'skills' => ['nullable', 'string', 'max:1000'],
            'livelihood_programs' => ['nullable', 'string', 'max:1000'],
            // Special Qualifications & Community
            'special_qualifications' => ['nullable', 'string', 'max:1000'],
            'community_activities' => ['nullable', 'string', 'max:1000'],
            // Emergency Contact
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_number' => ['nullable', 'string', 'max:20'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:100'],
            // Additional
            'years_of_residency' => ['nullable', 'integer', 'min:0', 'max:120'],
            'blood_type' => ['nullable', 'string', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'household_id.required' => 'Please select a household. Every resident must belong to a household.',
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'gender.required' => 'Gender is required.',
            'birthdate.required' => 'Birth date is required.',
            'birthdate.before' => 'Birth date cannot be in the future.',
            'civil_status.required' => 'Civil status is required.',
            'email.email' => 'Please enter a valid email address.',
            'monthly_income.numeric' => 'Monthly income must be a valid number.',
            'years_of_experience.integer' => 'Years of experience must be a whole number.',
            'years_of_residency.integer' => 'Years of residency must be a whole number.',
        ];
    }
}
