<?php

use App\Services\EventService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Link every participation to its resident's household.
        DB::statement(
            'UPDATE participations
             SET household_id = (
                 SELECT residents.household_id FROM residents WHERE residents.id = participations.resident_id
             )
             WHERE household_id IS NULL'
        );

        // Remove rows that cannot be tied to a household.
        DB::table('participations')->whereNull('household_id')->delete();

        // Collapse existing per-resident rows into one row per household per event.
        $duplicates = DB::table('participations')
            ->select('event_id', 'household_id')
            ->groupBy('event_id', 'household_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $keepId = DB::table('participations')
                ->where('event_id', $duplicate->event_id)
                ->where('household_id', $duplicate->household_id)
                ->orderByRaw(
                    'CASE WHEN remarks IS NULL OR remarks != ? THEN 0 ELSE 1 END',
                    [EventService::AUTO_GENERATED_REMARK]
                )
                ->orderByDesc('id')
                ->value('id');

            DB::table('participations')
                ->where('event_id', $duplicate->event_id)
                ->where('household_id', $duplicate->household_id)
                ->where('id', '!=', $keepId)
                ->delete();
        }

        if (Schema::hasIndex('participations', ['event_id', 'resident_id'])) {
            Schema::table('participations', function (Blueprint $table): void {
                $table->dropUnique(['event_id', 'resident_id']);
            });
        }

        Schema::table('participations', function (Blueprint $table): void {
            $table->unsignedBigInteger('resident_id')->nullable()->change();
        });

        // The household foreign key was declared ON DELETE SET NULL, which MySQL
        // forbids on a NOT NULL column, so swap it for ON DELETE CASCADE.
        if ($this->isMySql()) {
            if ($this->householdForeignKeyExists()) {
                Schema::table('participations', function (Blueprint $table): void {
                    $table->dropForeign(['household_id']);
                });
            }
        }

        Schema::table('participations', function (Blueprint $table): void {
            $table->unsignedBigInteger('household_id')->nullable(false)->change();
        });

        if ($this->isMySql() && ! $this->householdForeignKeyExists()) {
            Schema::table('participations', function (Blueprint $table): void {
                $table->foreign('household_id')->references('id')->on('households')->onDelete('cascade');
            });
        }

        if (! Schema::hasIndex('participations', ['event_id', 'household_id'])) {
            Schema::table('participations', function (Blueprint $table): void {
                $table->unique(['event_id', 'household_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('participations', function (Blueprint $table): void {
            $table->dropUnique(['event_id', 'household_id']);
        });

        if ($this->isMySql() && $this->householdForeignKeyExists()) {
            Schema::table('participations', function (Blueprint $table): void {
                $table->dropForeign(['household_id']);
            });
        }

        // Manual household rows have no resident; remove them so the old
        // per-resident NOT NULL + unique constraints can be restored.
        DB::table('participations')->whereNull('resident_id')->delete();

        Schema::table('participations', function (Blueprint $table): void {
            $table->unsignedBigInteger('resident_id')->nullable(false)->change();
        });

        Schema::table('participations', function (Blueprint $table): void {
            $table->unsignedBigInteger('household_id')->nullable()->change();
        });

        if ($this->isMySql() && ! $this->householdForeignKeyExists()) {
            Schema::table('participations', function (Blueprint $table): void {
                $table->foreign('household_id')->references('id')->on('households')->onDelete('set null');
            });
        }

        Schema::table('participations', function (Blueprint $table): void {
            $table->unique(['event_id', 'resident_id']);
        });
    }

    private function isMySql(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }

    private function householdForeignKeyExists(): bool
    {
        $row = DB::selectOne(
            "SELECT COUNT(*) AS aggregate
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'participations'
               AND COLUMN_NAME = 'household_id'
               AND REFERENCED_TABLE_NAME IS NOT NULL"
        );

        return (int) $row->aggregate > 0;
    }
};
