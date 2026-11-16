<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A staged update or delete request for a household or resident.
 *
 * The payload is only applied to the target record after the Admin and
 * then the Super Admin approve it; a rejected request is never applied.
 */
class PendingChange extends Model
{
    public const ACTION_UPDATE = 'update';

    public const ACTION_DELETE = 'delete';

    protected $fillable = [
        'target_type',
        'target_id',
        'target_label',
        'village_id',
        'action',
        'payload',
        'submitted_by',
        'submitter_role',
        'approval_status',
        'admin_approved_by',
        'admin_approved_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'remarks',
    ];

    protected $casts = [
        'payload' => 'array',
        'admin_approved_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class, 'village_id');
    }

    public function adminApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_approved_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Resolve the live target record (soft-deleted records return null).
     */
    public function resolveTarget(): Household|Resident|null
    {
        if ($this->target_type === 'household') {
            return Household::find($this->target_id);
        }

        if ($this->target_type === 'resident') {
            return Resident::find($this->target_id);
        }

        return null;
    }

    public function getTargetTypeLabelAttribute(): string
    {
        return $this->target_type === 'household' ? 'Household' : 'Resident';
    }

    public function getStageStatusLabelAttribute(): string
    {
        return match ($this->approval_status) {
            'pending' => 'Awaiting Admin Approval',
            'admin_approved' => 'Awaiting Final Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'superseded' => 'Superseded by a newer request',
            default => ucfirst((string) $this->approval_status),
        };
    }

    public function getActionLabelAttribute(): string
    {
        return $this->action === self::ACTION_DELETE ? 'Delete Request' : 'Update Request';
    }
}
