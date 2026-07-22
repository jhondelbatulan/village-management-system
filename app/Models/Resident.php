<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resident extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'household_id',
        'village_id',
        'resident_code',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'gender',
        'birthdate',
        'age',
        'civil_status',
        'nationality',
        'occupation',
        'educational_attainment',
        'religion',
        'contact_number',
        'email',
        'profile_picture',
        'relationship_to_head',
        'voter_status',
        'is_household_head',
        'status',
        'approval_status',
        'submitted_by',
        'approved_by',
        'approved_at',
        'remarks',
        'admin_approved_by',
        'admin_approved_at',
        // Education
        'education_level',
        'school_name',
        'grade_level',
        'is_scholarship',
        'scholarship_type',
        // Employment
        'employment_status',
        'employer_name',
        'job_title',
        'years_of_experience',
        // Income
        'income_source',
        'monthly_income',
        'monthly_income_range',
        // Special Population
        'is_pwd',
        'pwd_type',
        'is_solo_parent',
        'is_senior_citizen',
        'is_youth',
        'is_student',
        'is_ofw',
        'is_infant',
        // Government Memberships
        'is_pagibig_member',
        'is_philhealth_member',
        'is_sss_member',
        'is_gsis_member',
        // Government Assistance
        'is_4ps_member',
        'is_senior_pensioner',
        'other_gov_assistance',
        'is_tupad_beneficiary',
        'is_akap_beneficiary',
        'is_educational_assistance',
        'is_emergency_assistance',
        'is_slp_beneficiary',
        // Government IDs
        'is_national_id_holder',
        'is_senior_id_holder',
        'is_pwd_id_holder',
        'is_solo_parent_id_holder',
        // Social Categories
        'is_indigenous',
        'is_veteran',
        // Community Participation
        'is_disaster_volunteer',
        'is_village_volunteer',
        // Skills & Livelihood
        'skills',
        'primary_skill',
        'secondary_skill',
        'livelihood_programs',
        // Special Qualifications & Community
        'special_qualifications',
        'community_activities',
        // Emergency Contact
        'emergency_contact_name',
        'emergency_contact_number',
        'emergency_contact_relationship',
        // Additional
        'years_of_residency',
        'blood_type',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'age' => 'integer',
            'voter_status' => 'boolean',
            'is_household_head' => 'boolean',
            'approved_at' => 'datetime',
            'admin_approved_at' => 'datetime',
            // Education
            'is_scholarship' => 'boolean',
            // Employment
            'years_of_experience' => 'integer',
            // Income
            'monthly_income' => 'decimal:2',
            // Special Population
            'is_pwd' => 'boolean',
            'is_solo_parent' => 'boolean',
            'is_senior_citizen' => 'boolean',
            'is_youth' => 'boolean',
            'is_student' => 'boolean',
            'is_ofw' => 'boolean',
            'is_infant' => 'boolean',
            // Government
            'is_pagibig_member' => 'boolean',
            'is_philhealth_member' => 'boolean',
            'is_sss_member' => 'boolean',
            'is_gsis_member' => 'boolean',
            'is_4ps_member' => 'boolean',
            'is_senior_pensioner' => 'boolean',
            'is_tupad_beneficiary' => 'boolean',
            'is_akap_beneficiary' => 'boolean',
            'is_educational_assistance' => 'boolean',
            'is_emergency_assistance' => 'boolean',
            'is_slp_beneficiary' => 'boolean',
            'is_national_id_holder' => 'boolean',
            'is_senior_id_holder' => 'boolean',
            'is_pwd_id_holder' => 'boolean',
            'is_solo_parent_id_holder' => 'boolean',
            'is_indigenous' => 'boolean',
            'is_veteran' => 'boolean',
            'is_disaster_volunteer' => 'boolean',
            'is_village_volunteer' => 'boolean',
            // Additional
            'years_of_residency' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Resident $resident) {
            if ($resident->birthdate) {
                $resident->age = Carbon::parse($resident->birthdate)->age;
                $resident->is_senior_citizen = $resident->age >= 60;
                $resident->is_youth = $resident->age >= 15 && $resident->age <= 30;
                $resident->is_infant = $resident->age <= 5;
            }
        });
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function adminApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_approved_by');
    }

    public function eventResponses(): HasMany
    {
        return $this->hasMany(EventResponse::class);
    }

    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class);
    }

    public function getFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->suffix,
        ]);
        return implode(' ', $parts);
    }

    public function getProfilePictureUrlAttribute(): string
    {
        if ($this->profile_picture) {
            return asset('storage/' . $this->profile_picture);
        }
        return asset(config('app_settings.assets.default_profile'));
    }

    public function getMonthlyIncomeFormattedAttribute(): ?string
    {
        if ($this->monthly_income === null) {
            return null;
        }
        return '₱' . number_format((float) $this->monthly_income, 2);
    }

    public function getSpecialPopulationTypesAttribute(): array
    {
        $types = [];
        if ($this->is_pwd) $types[] = 'PWD';
        if ($this->is_solo_parent) $types[] = 'Solo Parent';
        if ($this->is_senior_citizen) $types[] = 'Senior Citizen';
        if ($this->is_youth) $types[] = 'Youth';
        if ($this->is_student) $types[] = 'Student';
        if ($this->is_ofw) $types[] = 'OFW';
        if ($this->is_infant) $types[] = 'Infant/Child';
        if ($this->is_indigenous) $types[] = 'Indigenous Person';
        if ($this->is_veteran) $types[] = 'Veteran';
        return $types;
    }

    public function getGovernmentMembershipsAttribute(): array
    {
        $memberships = [];
        if ($this->is_pagibig_member) $memberships[] = 'Pag-IBIG';
        if ($this->is_philhealth_member) $memberships[] = 'PhilHealth';
        if ($this->is_sss_member) $memberships[] = 'SSS';
        if ($this->is_gsis_member) $memberships[] = 'GSIS';
        if ($this->is_national_id_holder) $memberships[] = 'National ID';
        if ($this->is_senior_id_holder) $memberships[] = 'Senior Citizen ID';
        if ($this->is_pwd_id_holder) $memberships[] = 'PWD ID';
        if ($this->is_solo_parent_id_holder) $memberships[] = 'Solo Parent ID';
        return $memberships;
    }

    public function getGovernmentAssistanceTypesAttribute(): array
    {
        $types = [];
        if ($this->is_4ps_member) $types[] = '4Ps';
        if ($this->is_senior_pensioner) $types[] = 'Senior Pension';
        if ($this->is_tupad_beneficiary) $types[] = 'TUPAD';
        if ($this->is_akap_beneficiary) $types[] = 'AKAP';
        if ($this->is_educational_assistance) $types[] = 'Educational Assistance';
        if ($this->is_emergency_assistance) $types[] = 'Emergency Assistance';
        if ($this->is_slp_beneficiary) $types[] = 'SLP';
        if ($this->is_scholarship) $types[] = 'Scholarship';
        if ($this->other_gov_assistance) $types[] = $this->other_gov_assistance;
        return $types;
    }

    public static function generateResidentCode(): string
    {
        $lastResident = self::withTrashed()->latest('id')->first();
        $nextNumber = $lastResident ? intval(substr($lastResident->resident_code, 4)) + 1 : 1;
        return 'RES-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
