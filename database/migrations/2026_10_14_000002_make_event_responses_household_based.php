<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_responses', function (Blueprint $table): void {
            $table->foreignId('household_id')->nullable()->after('resident_id')->constrained()->nullOnDelete();
        });

        // Link every response to its resident's household.
        DB::statement(
            'UPDATE event_responses
             SET household_id = (
                 SELECT residents.household_id FROM residents WHERE residents.id = event_responses.resident_id
             )
             WHERE household_id IS NULL'
        );

        // Remove rows that cannot be tied to a household.
        DB::table('event_responses')->whereNull('household_id')->delete();

        if (Schema::hasIndex('event_responses', ['event_id', 'resident_id'])) {
            Schema::table('event_responses', function (Blueprint $table): void {
                $table->dropUnique(['event_id', 'resident_id']);
            });
        }

        Schema::table('event_responses', function (Blueprint $table): void {
            $table->unsignedBigInteger('resident_id')->nullable()->change();
        });

        if (! Schema::hasIndex('event_responses', ['event_id', 'household_id'])) {
            Schema::table('event_responses', function (Blueprint $table): void {
                $table->unique(['event_id', 'household_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('event_responses', function (Blueprint $table): void {
            $table->dropUnique(['event_id', 'household_id']);
        });

        // Household-level rows have no resident; remove them so the old
        // per-resident NOT NULL + unique constraints can be restored.
        DB::table('event_responses')->whereNull('resident_id')->delete();

        Schema::table('event_responses', function (Blueprint $table): void {
            $table->unsignedBigInteger('resident_id')->nullable(false)->change();
        });

        Schema::table('event_responses', function (Blueprint $table): void {
            $table->unique(['event_id', 'resident_id']);
        });

        Schema::table('event_responses', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('household_id');
        });
    }
};
