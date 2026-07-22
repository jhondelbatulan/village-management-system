<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            // Additional Government Assistance Programs
            $table->boolean('is_tupad_beneficiary')->default(false)->after('other_gov_assistance');
            $table->boolean('is_akap_beneficiary')->default(false)->after('is_tupad_beneficiary');
            $table->boolean('is_educational_assistance')->default(false)->after('is_akap_beneficiary');
            $table->boolean('is_emergency_assistance')->default(false)->after('is_educational_assistance');
            $table->boolean('is_slp_beneficiary')->default(false)->after('is_emergency_assistance');
            // Government IDs
            $table->boolean('is_national_id_holder')->default(false)->after('is_slp_beneficiary');
            $table->boolean('is_senior_id_holder')->default(false)->after('is_national_id_holder');
            $table->boolean('is_pwd_id_holder')->default(false)->after('is_senior_id_holder');
            $table->boolean('is_solo_parent_id_holder')->default(false)->after('is_pwd_id_holder');
            // Social Categories
            $table->boolean('is_indigenous')->default(false)->after('is_solo_parent_id_holder');
            $table->boolean('is_veteran')->default(false)->after('is_indigenous');
            // Community Participation
            $table->boolean('is_disaster_volunteer')->default(false)->after('is_veteran');
            $table->boolean('is_village_volunteer')->default(false)->after('is_disaster_volunteer');
        });
    }

    public function down(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            $table->dropColumn([
                'is_tupad_beneficiary', 'is_akap_beneficiary', 'is_educational_assistance',
                'is_emergency_assistance', 'is_slp_beneficiary',
                'is_national_id_holder', 'is_senior_id_holder', 'is_pwd_id_holder', 'is_solo_parent_id_holder',
                'is_indigenous', 'is_veteran',
                'is_disaster_volunteer', 'is_village_volunteer',
            ]);
        });
    }
};
