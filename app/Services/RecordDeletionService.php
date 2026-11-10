<?php

namespace App\Services;

use App\Models\Household;
use App\Models\Resident;
use Illuminate\Support\Facades\Storage;

/**
 * Permanently removes a household or resident together with every record
 * that belongs to it.
 *
 * Used when an approval request is rejected (and when an archived record is
 * force-deleted) so nothing lingers in lists, statistics, GIS layers, or
 * dashboards.
 */
class RecordDeletionService
{
    public function purgeHousehold(Household $household): void
    {
        $household->participations()->delete();
        $household->billings()->delete();
        $household->payments()->delete();
        $household->receipts()->delete();
        $household->paymentAmounts()->delete();

        Resident::withTrashed()
            ->where('household_id', $household->id)
            ->each(function (Resident $resident): void {
                $this->purgeResident($resident);
            });

        $household->forceDelete();
    }

    public function purgeResident(Resident $resident): void
    {
        $resident->participations()->delete();
        $resident->eventResponses()->delete();

        if ($resident->profile_picture) {
            try {
                Storage::disk('public')->delete($resident->profile_picture);
            } catch (\Throwable) {
                // A missing file must never block the record's removal.
            }
        }

        $resident->forceDelete();
    }
}
