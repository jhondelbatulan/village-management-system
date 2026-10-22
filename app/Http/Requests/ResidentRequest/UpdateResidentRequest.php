<?php

namespace App\Http\Requests\ResidentRequest;

use App\Rules\ProperName;
use App\Rules\SafeText;
use Illuminate\Foundation\Http\FormRequest;

class UpdateResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'household_id' => ['required', 'exists:households,id'],
            'first_name' => ['required', 'string', 'max:255', new ProperName],
            'middle_name' => ['nullable', 'string', 'max:255', new ProperName],
            'last_name' => ['required', 'string', 'max:255', new ProperName],
            'suffix' => ['nullable', 'string', 'max:50', new SafeText],
            'gender' => ['required', 'in:Male,Female,Other'],
            'birthdate' => ['required', 'date', 'before:today'],
            'civil_status' => ['required', 'in:Single,Married,Widowed,Separated,Divorced'],
            'nationality' => ['nullable', 'string', 'max:100', new SafeText],
            'occupation' => ['nullable', 'string', 'max:255', new SafeText],
            'educational_attainment' => ['nullable', 'string', 'max:255'],
            'religion' => ['nullable', 'string', 'max:100', new SafeText],
            'contact_number' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'relationship_to_head' => ['nullable', 'string', 'max:100', new SafeText],
            'voter_status' => ['nullable', 'boolean'],
            'is_household_head' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,inactive,deceased,transferred'],
            // Education
            'education_level' => ['nullable', 'string', 'max:100'],
            'school_name' => ['nullable', 'string', 'max:255', new SafeText],
            'grade_level' => ['nullable', 'string', 'max:100', new SafeText],
            'is_scholarship' => ['nullable', 'boolean'],
            'scholarship_type' => ['nullable', 'string', 'max:255', new SafeText],
            // Employment
            'employment_status' => ['nullable', 'string', 'max:50'],
            'employer_name' => ['nullable', 'string', 'max:255', new SafeText],
            'job_title' => ['nullable', 'string', 'max:255', new SafeText],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:60'],
            // Income
            'income_source' => ['nullable', 'string', 'max:100'],
            'monthly_income' => ['nullable', 'numeric', 'min:0'],
            'monthly_income_range' => ['nullable', 'string', 'max:100'],
            // Special Population
            'is_pwd' => ['nullable', 'boolean'],
            'pwd_type' => ['nullable', 'string', 'max:255', new SafeText],
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
            'other_gov_assistance' => ['nullable', 'string', 'max:255', new SafeText],
            // Skills & Livelihood
            'primary_skill' => ['nullable', 'string', 'max:100', new SafeText],
            'secondary_skill' => ['nullable', 'string', 'max:100', new SafeText],
            'skills' => ['nullable', 'string', 'max:1000', new SafeText],
            'livelihood_programs' => ['nullable', 'string', 'max:1000', new SafeText],
            // Special Qualifications & Community
            'special_qualifications' => ['nullable', 'string', 'max:1000', new SafeText],
            'community_activities' => ['nullable', 'string', 'max:1000', new SafeText],
            // Government IDs
            'is_national_id_holder' => ['nullable', 'boolean'],
            'is_senior_id_holder' => ['nullable', 'boolean'],
            'is_pwd_id_holder' => ['nullable', 'boolean'],
            'is_solo_parent_id_holder' => ['nullable', 'boolean'],
            // Additional Ethnicity / Veteran
            'is_indigenous' => ['nullable', 'boolean'],
            'is_veteran' => ['nullable', 'boolean'],
            'is_disaster_volunteer' => ['nullable', 'boolean'],
            'is_village_volunteer' => ['nullable', 'boolean'],
            'is_tupad_beneficiary' => ['nullable', 'boolean'],
            'is_akap_beneficiary' => ['nullable', 'boolean'],
            'is_educational_assistance' => ['nullable', 'boolean'],
            'is_emergency_assistance' => ['nullable', 'boolean'],
            'is_slp_beneficiary' => ['nullable', 'boolean'],
            // Emergency Contact
            'emergency_contact_name' => ['nullable', 'string', 'max:255', new SafeText],
            'emergency_contact_number' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:100', new SafeText],
            // Additional
            'years_of_residency' => ['nullable', 'integer', 'min:0', 'max:120'],
            'blood_type' => ['nullable', 'string', 'max:10', 'regex:/^(A|B|AB|O)[+-]$|^Unknown$/i'],
        ];
    }
}
