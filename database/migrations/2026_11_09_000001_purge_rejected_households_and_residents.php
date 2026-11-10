<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Earlier versions flagged rejected requests instead of deleting them, so
 * rejected households and residents stayed in the database and kept showing
 * up on dashboards. Purge them once so only real records remain.
 */
return new class extends Migration
{
    /** Tables that hang off a household through a household_id column. */
    private const HOUSEHOLD_TABLES = [
        'participations',
        'announcement_responses',
        'event_responses',
        'household_billings',
        'household_payments',
        'payment_receipts',
        'household_payment_amounts',
        'payment_penalties',
        'online_payments',
        'residents',
    ];

    /** Tables that hang off a resident through a resident_id column. */
    private const RESIDENT_TABLES = [
        'participations',
        'event_responses',
        'announcement_responses',
    ];

    public function up(): void
    {
        $rejectedHouseholdIds = DB::table('households')
            ->where('approval_status', 'rejected')
            ->pluck('id');

        // Standalone rejected residents plus every resident of a rejected
        // household, regardless of that resident's own status.
        $rejectedResidentIds = DB::table('residents')
            ->where('approval_status', 'rejected')
            ->orWhereIn('household_id', $rejectedHouseholdIds)
            ->pluck('id');

        if ($rejectedResidentIds->isNotEmpty()) {
            // Rows keyed by resident cascade at the database level, but delete
            // explicitly so the cleanup also works where FKs are not enforced.
            foreach (self::RESIDENT_TABLES as $table) {
                $this->deleteWhere($table, 'resident_id', $rejectedResidentIds);
            }

            DB::table('residents')->whereIn('id', $rejectedResidentIds)->delete();
        }

        if ($rejectedHouseholdIds->isNotEmpty()) {
            foreach (self::HOUSEHOLD_TABLES as $table) {
                $this->deleteWhere($table, 'household_id', $rejectedHouseholdIds);
            }

            DB::table('households')->whereIn('id', $rejectedHouseholdIds)->delete();
        }

        // Keep the stored village counters in sync with the purged rows.
        DB::table('villages')->orderBy('id')->pluck('id')->each(function ($villageId): void {
            $village = app(\App\Models\Village::class)->find($villageId);
            if ($village) {
                $village->recalculateCounts();
            }
        });
    }

    public function down(): void
    {
        // Purged records cannot be restored.
    }

    /**
     * @param  \Illuminate\Support\Collection<int, int|string>  $keys
     */
    private function deleteWhere(string $table, string $column, $keys): void
    {
        if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
            DB::table($table)->whereIn($column, $keys)->delete();
        }
    }
};
