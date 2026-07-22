<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentPenalty extends Model
{
    protected $table = 'payment_penalties';

    protected $fillable = [
        'billing_id',
        'amount',
        'reason',
        'imposed_date',
        'imposed_by',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'imposed_date' => 'date',
    ];

    public function billing(): BelongsTo
    {
        return $this->belongsTo(Billing::class, 'billing_id');
    }

    public function imposedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imposed_by');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active' => 'bg-danger',
            'waived' => 'bg-secondary',
            'paid' => 'bg-success',
            default => 'bg-secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status);
    }
}
