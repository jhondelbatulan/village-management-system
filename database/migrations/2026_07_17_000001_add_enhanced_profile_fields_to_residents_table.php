<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            // Education
            $table->string('education_level')->nullable()->after('educational_attainment');
            $table->string('school_name')->nullable()->after('education_level');
            $table->string('grade_level')->nullable()->after('school_name');
            $table->boolean('is_scholarship')->default(false)->after('grade_level');
            $table->string('scholarship_type')->nullable()->after('is_scholarship');

            // Employment
            $table->string('employment_status')->nullable()->after('occupation');
            $table->string('employer_name')->nullable()->after('employment_status');
            $table->string('job_title')->nullable()->after('employer_name');
            $table->integer('years_of_experience')->nullable()->after('job_title');

            // Income
            $table->string('income_source')->nullable()->after('years_of_experience');
            $table->decimal('monthly_income', 12, 2)->nullable()->after('income_source');
            $table->string('monthly_income_range')->nullable()->after('monthly_income');

            // Special Population
            $table->boolean('is_pwd')->default(false)->after('is_household_head');
            $table->string('pwd_type')->nullable()->after('is_pwd');
            $table->boolean('is_solo_parent')->default(false)->after('pwd_type');
            $table->boolean('is_senior_citizen')->default(false)->after('is_solo_parent');
            $table->boolean('is_youth')->default(false)->after('is_senior_citizen');
            $table->boolean('is_student')->default(false)->after('is_youth');
            $table->boolean('is_ofw')->default(false)->after('is_student');
            $table->boolean('is_infant')->default(false)->after('is_ofw');

            // Government Memberships
            $table->boolean('is_pagibig_member')->default(false)->after('is_infant');
            $table->boolean('is_philhealth_member')->default(false)->after('is_pagibig_member');
            $table->boolean('is_sss_member')->default(false)->after('is_philhealth_member');
            $table->boolean('is_gsis_member')->default(false)->after('is_sss_member');

            // Government Assistance
            $table->boolean('is_4ps_member')->default(false)->after('is_gsis_member');
            $table->boolean('is_senior_pensioner')->default(false)->after('is_4ps_member');
            $table->string('other_gov_assistance')->nullable()->after('is_senior_pensioner');

            // Skills & Livelihood
            $table->text('skills')->nullable()->after('other_gov_assistance');
            $table->text('livelihood_programs')->nullable()->after('skills');

            // Special Qualifications & Community
            $table->text('special_qualifications')->nullable()->after('livelihood_programs');
            $table->text('community_activities')->nullable()->after('special_qualifications');

            // Emergency Contact
            $table->string('emergency_contact_name')->nullable()->after('community_activities');
            $table->string('emergency_contact_number')->nullable()->after('emergency_contact_name');
            $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_number');

            // Additional
            $table->integer('years_of_residency')->nullable()->after('emergency_contact_relationship');
            $table->string('blood_type')->nullable()->after('years_of_residency');
        });
    }

    public function down(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            $table->dropColumn([
                'education_level', 'school_name', 'grade_level', 'is_scholarship', 'scholarship_type',
                'employment_status', 'employer_name', 'job_title', 'years_of_experience',
                'income_source', 'monthly_income', 'monthly_income_range',
                'is_pwd', 'pwd_type', 'is_solo_parent', 'is_senior_citizen', 'is_youth',
                'is_student', 'is_ofw', 'is_infant',
                'is_pagibig_member', 'is_philhealth_member', 'is_sss_member', 'is_gsis_member',
                'is_4ps_member', 'is_senior_pensioner', 'other_gov_assistance',
                'skills', 'livelihood_programs', 'special_qualifications', 'community_activities',
                'emergency_contact_name', 'emergency_contact_number', 'emergency_contact_relationship',
                'years_of_residency', 'blood_type',
            ]);
        });
    }
};
