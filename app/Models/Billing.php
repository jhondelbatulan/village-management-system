<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Billing extends Model
{
    use SoftDeletes;

    protected $table = 'household_billings';

    protected $fillable = [
        'billing_number',
        'household_id',
        'village_id',
        'payment_type_id',
        'billing_period',
        'due_date',
        'amount',
        'penalty',
        'discount',
        'total_amount',
        'status',
        'created_by',
        'remarks',
    ];

    protected $casts = [
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'penalty' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(HouseholdPayment::class, 'billing_id');
    }

    public function penalties(): HasMany
    {
        return $this->hasMany(PaymentPenalty::class, 'billing_id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(PaymentReceipt::class, 'billing_id');
    }

    public static function generateBillingNumber(string $prefix = 'BILL'): string
    {
        $date = now();
        $month = $date->format('Ym');
        $sequence = 1;

        do {
            $number = sprintf('%s-%s-%04d', $prefix, $month, $sequence);
            $sequence++;
        } while (static::withTrashed()->where('billing_number', $number)->exists());

        return $number;
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()
            ->sum('total_paid');
    }

    public function getBalanceAttribute(): float
    {
        return $this->total_amount - $this->total_paid;
    }

    public function getStatusBadgeAttribute(): string
    {
        $status = $this->status === 'pending' && $this->due_date?->lt(now()->startOfDay()) ? 'overdue' : $this->status;

        return match ($status) {
            'draft' => 'bg-secondary',
            'pending' => 'bg-danger',
            'paid' => 'bg-success',
            'partially_paid' => 'bg-danger',
            'overdue' => 'bg-warning text-dark',
            'cancelled' => 'bg-dark',
            default => 'bg-secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        $status = $this->status === 'pending' && $this->due_date?->lt(now()->startOfDay()) ? 'overdue' : $this->status;

        return match ($status) {
            'draft' => 'Draft',
            'pending' => 'Unpaid',
            'paid' => 'Fully Paid',
            'partially_paid' => 'Unpaid',
            'overdue' => 'Overdue',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    public function scopeForVillage($query, int $villageId)
    {
        return $query->where('village_id', $villageId);
    }

    public function scopeForPeriod($query, string $period)
    {
        return $query->where('billing_period', $period);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->where('status', 'pending')
                  ->where('due_date', '<', now());
            });
    }
}
