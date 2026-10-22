<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Link every response to its resident's household.
        DB::statement(
            'UPDATE announcement_responses
             SET household_id = (
                 SELECT residents.household_id FROM residents WHERE residents.id = announcement_responses.resident_id
             )
             WHERE household_id IS NULL'
        );

        // Remove rows that cannot be tied to a household.
        DB::table('announcement_responses')->whereNull('household_id')->delete();

        // On MySQL the composite unique index doubles as the lookup index for
        // the announcement foreign key, so both foreign keys must be dropped
        // before the index can be removed.
        if ($this->isMySql()) {
            Schema::table('announcement_responses', function (Blueprint $table): void {
                $table->dropForeign(['announcement_id']);
                $table->dropForeign(['resident_id']);
            });
        }

        if (Schema::hasIndex('announcement_responses', ['announcement_id', 'resident_id'])) {
            Schema::table('announcement_responses', function (Blueprint $table): void {
                $table->dropUnique(['announcement_id', 'resident_id']);
            });
        }

        Schema::table('announcement_responses', function (Blueprint $table): void {
            $table->unsignedBigInteger('resident_id')->nullable()->change();
        });

        if ($this->isMySql()) {
            Schema::table('announcement_responses', function (Blueprint $table): void {
                $table->foreign('announcement_id')->references('id')->on('announcements')->cascadeOnDelete();
                $table->foreign('resident_id')->references('id')->on('residents')->cascadeOnDelete();
            });
        }

        if (! Schema::hasIndex('announcement_responses', ['announcement_id', 'household_id'])) {
            Schema::table('announcement_responses', function (Blueprint $table): void {
                $table->unique(['announcement_id', 'household_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('announcement_responses', function (Blueprint $table): void {
            $table->dropUnique(['announcement_id', 'household_id']);
        });

        // Household-level rows have no resident; remove them so the old
        // per-resident NOT NULL + unique constraints can be restored.
        DB::table('announcement_responses')->whereNull('resident_id')->delete();

        Schema::table('announcement_responses', function (Blueprint $table): void {
            $table->unsignedBigInteger('resident_id')->nullable(false)->change();
        });

        Schema::table('announcement_responses', function (Blueprint $table): void {
            $table->unique(['announcement_id', 'resident_id']);
        });
    }

    private function isMySql(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }
};
