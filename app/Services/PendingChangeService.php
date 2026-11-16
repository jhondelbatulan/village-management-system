<?php

namespace App\Services;

use App\Models\Household;
use App\Models\PendingChange;
use App\Models\Resident;
use App\Models\Village;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Staged household/resident changes.
 *
 * Resident and secretary edits/deletes never touch the record directly:
 * they are queued as PendingChange rows and only applied after the Admin
 * (stage 1) and the Super Admin (stage 2) approve them. Rejected requests
 * are discarded without ever being saved.
 */
class PendingChangeService
{
    public function submitUpdate(Model $target, string $targetType, array $payload): PendingChange
    {
        return $this->stage($target, $targetType, PendingChange::ACTION_UPDATE, $payload);
    }

    public function submitDelete(Model $target, string $targetType): PendingChange
    {
        return $this->stage($target, $targetType, PendingChange::ACTION_DELETE, []);
    }

    /**
     * Apply a finally-approved request to the target record.
     * Called only from the Super Admin final approval, inside a transaction.
     */
    public function apply(PendingChange $change, Model $target): void
    {
        if ($change->action === PendingChange::ACTION_DELETE) {
            $this->applyDelete($target);

            return;
        }

        $this->applyUpdate($target, $change->payload ?? []);
    }

    /**
     * Throw away a staged profile picture that will never be applied
     * (request rejected or replaced by a newer submission).
     */
    public function discardStagedPicture(PendingChange $change): void
    {
        $path = $change->payload['profile_picture'] ?? null;

        if (! $path) {
            return;
        }

        // Never delete a picture that is already live on the record.
        $target = $change->resolveTarget();
        if ($target instanceof Resident && $target->profile_picture === $path) {
            return;
        }

        $full = Storage::disk('public')->path($path);
        if (is_file($full)) {
            @unlink($full);
        }
    }

    private function stage(Model $target, string $targetType, string $action, array $payload): PendingChange
    {
        $user = auth()->user();

        // Keep every submission immutable for audit. A newer proposal
        // supersedes prior open proposals from the same submitter.
        $openChanges = PendingChange::where('target_type', $targetType)
            ->where('target_id', $target->getKey())
            ->where('action', $action)
            ->where('submitted_by', $user?->id ?? 0)
            ->whereIn('approval_status', ['pending', 'admin_approved'])
            ->get();

        foreach ($openChanges as $openChange) {
            $this->discardStagedPicture($openChange);
            $openChange->update([
                'approval_status' => 'superseded',
                'remarks' => 'Superseded by a newer submission.',
            ]);
        }

        $change = PendingChange::create([
            'target_type' => $targetType,
            'target_id' => $target->getKey(),
            'target_label' => $this->labelFor($target, $targetType),
            'village_id' => $target->village_id,
            'action' => $action,
            'payload' => $action === PendingChange::ACTION_UPDATE ? $payload : [],
            'submitted_by' => $user?->id ?? 0,
            'submitter_role' => $user?->role?->role_slug,
            'approval_status' => 'pending',
        ]);

        // Stage 1: the village Admin reviews first.
        app(NotificationService::class)->notifyAdmins(
            $change->village_id,
            'Approval Required',
            $this->notificationMessage($change, $user?->full_name ?? 'A staff member'),
            $change->action === PendingChange::ACTION_DELETE
                ? route('approvals.deletes')
                : route('approvals.updates'),
            'warning'
        );

        return $change;
    }

    private function notificationMessage(PendingChange $change, string $submitter): string
    {
        $noun = $change->target_type === 'household' ? 'Household' : 'Resident';
        $label = $change->target_label ?? ('#' . $change->target_id);
        $action = $change->action === PendingChange::ACTION_DELETE ? 'Delete' : 'Update';

        return "{$action} request for {$noun} \"{$label}\" submitted by {$submitter} needs your approval.";
    }

    private function applyUpdate(Model $target, array $data): void
    {
        if ($target instanceof Resident) {
            if ($target->approval_status === 'rejected') {
                $data = array_merge([
                    'approval_status' => 'draft',
                    'submitted_by' => null,
                    'approved_by' => null,
                    'approved_at' => null,
                    'admin_approved_by' => null,
                    'admin_approved_at' => null,
                    'remarks' => null,
                ], $data);
            }

            // A staged picture was stored under a pending name; swap the live
            // picture only now that the change is finally approved.
            if (isset($data['profile_picture'])
                && $target->profile_picture
                && $target->profile_picture !== $data['profile_picture']) {
                $old = Storage::disk('public')->path($target->profile_picture);
                if (is_file($old)) {
                    @unlink($old);
                }
            }

            $household = Household::find($data['household_id'] ?? $target->household_id);
            if ($household) {
                $data['village_id'] = $household->village_id;
            }

            $oldVillageId = $target->village_id;
            $target->update($data);

            $this->recalculateVillage($target->village_id);
            if ($oldVillageId != $target->village_id) {
                $this->recalculateVillage($oldVillageId);
            }

            AuditLogService::log('Apply Approved Update', 'Approvals', "Applied approved update to resident: {$target->full_name}");

            return;
        }

        if ($target instanceof Household) {
            if ($target->approval_status === 'rejected') {
                $data = array_merge([
                    'approval_status' => 'draft',
                    'submitted_by' => null,
                    'approved_by' => null,
                    'approved_at' => null,
                    'admin_approved_by' => null,
                    'admin_approved_at' => null,
                    'remarks' => null,
                ], $data);
            }

            $target->update($data);

            $this->recalculateVillage($target->village_id);

            AuditLogService::log('Apply Approved Update', 'Approvals', "Applied approved update to household: {$target->household_number}");
        }
    }

    private function applyDelete(Model $target): void
    {
        if ($target instanceof Household) {
            $number = $target->household_number;

            $target->delete();

            // Archive every resident of this household (any approval status),
            // stamped with the household's deleted_at so restore can find them.
            Resident::withTrashed()
                ->where('household_id', $target->id)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => $target->deleted_at]);

            $this->recalculateVillage($target->village_id);

            AuditLogService::log('Archive Household', 'Household Management', "Archived household: {$number} and its residents.");

            app(NotificationService::class)->notifyHouseholdResidents(
                [$target->id],
                'warning',
                'Household Archived',
                "Your household ({$number}) has been archived by the administration.",
                route('households.my-household')
            );

            app(NotificationService::class)->notifyAdmins(
                $target->village_id,
                'Household Archived',
                "Household \"{$number}\" and its residents have been archived.",
                route('households.index'),
                'warning'
            );

            return;
        }

        if ($target instanceof Resident) {
            $name = $target->full_name;
            $code = $target->resident_code;
            $householdId = $target->household_id;

            $target->delete();

            $this->recalculateVillage($target->village_id);

            AuditLogService::log('Archive Resident', 'Resident Management', "Archived resident: {$name} ({$code})");

            if ($householdId) {
                app(NotificationService::class)->notifyHouseholdResidents(
                    [$householdId],
                    'warning',
                    'Resident Archived',
                    "A resident profile ({$name}) in your household has been archived.",
                    route('households.my-household')
                );
            }
        }
    }

    private function recalculateVillage(?int $villageId): void
    {
        if (! $villageId) {
            return;
        }

        $village = Village::find($villageId);
        $village?->recalculateCounts();
    }

    private function labelFor(Model $target, string $targetType): string
    {
        if ($targetType === 'household' && $target instanceof Household) {
            return (string) $target->household_number;
        }

        if ($target instanceof Resident) {
            return (string) $target->full_name;
        }

        return '#' . $target->getKey();
    }
}
