<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OnlinePayment extends Model
{
    protected $table = 'online_payments';

    protected $fillable = [
        'village_id',
        'billing_id',
        'household_id',
        'resident_id',
        'amount',
        'transaction_number',
        'proof_path',
        'status',
        'declined_reason',
        'reviewed_by',
        'reviewed_at',
        'payment_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public const STATUSES = ['pending', 'approved', 'declined'];

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function billing(): BelongsTo
    {
        return $this->belongsTo(Billing::class, 'billing_id');
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resident_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(HouseholdPayment::class, 'id', 'payment_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'bg-success',
            'declined' => 'bg-danger',
            default => 'bg-warning text-dark',
        };
    }

    /**
     * A billing has an active (not yet recorded) online payment request when
     * one is still pending review or was approved but not yet recorded as a
     * payment. Declined requests may be resubmitted by the resident.
     */
    public static function hasActiveFor(int $billingId): bool
    {
        return self::where('billing_id', $billingId)
            ->where(function ($query): void {
                $query->where('status', 'pending')
                    ->orWhere(function ($q): void {
                        $q->where('status', 'approved')->whereNull('payment_id');
                    });
            })
            ->exists();
    }
}
