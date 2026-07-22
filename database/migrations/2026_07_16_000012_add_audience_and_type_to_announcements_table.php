<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('audience_type')->default('all_residents')->after('is_pinned')->comment('all_villages, selected_villages, all_residents, entire_village, selected_households, specific_block, village_officers, outstanding_payments, fully_paid, event_participants');
            $table->json('target_villages')->nullable()->after('audience_type')->comment('Array of village IDs for multi-village targeting');
            $table->json('target_audience')->nullable()->after('target_villages')->comment('JSON: household_ids, block_number, etc.');
            $table->boolean('requires_attendance')->default(false)->after('target_audience');
            $table->string('announcement_type')->default('general')->after('category')->comment('general, payment_reminder, emergency, meeting, community, health, disaster, training, sports, attendance_required, others');
        });

        Schema::table('participations', function (Blueprint $table) {
            $table->enum('response', ['going', 'maybe', 'not_going'])->nullable()->after('status')->comment('Response for attendance-required announcements');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['audience_type', 'target_villages', 'target_audience', 'requires_attendance', 'announcement_type']);
        });

        Schema::table('participations', function (Blueprint $table) {
            $table->dropColumn('response');
        });
    }
};
