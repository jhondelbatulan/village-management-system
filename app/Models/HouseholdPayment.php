<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HouseholdPayment extends Model
{
    use SoftDeletes;

    protected $table = 'household_payments';

    protected $fillable = [
        'receipt_number',
        'billing_id',
        'billing_number_snapshot',
        'billing_amount_snapshot',
        'billing_period_snapshot',
        'billing_balance_snapshot',
        'household_id',
        'village_id',
        'payment_type_id',
        'amount',
        'penalty',
        'discount',
        'total_paid',
        'payment_date',
        'due_date',
        'payment_method',
        'reference_number',
        'payment_status',
        'received_by',
        'remarks',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'penalty' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'billing_amount_snapshot' => 'decimal:2',
        'billing_balance_snapshot' => 'decimal:2',
        'payment_date' => 'date',
        'due_date' => 'date',
    ];

    public function billing(): BelongsTo
    {
        return $this->belongsTo(Billing::class, 'billing_id');
    }

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

    public function receivedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function receipt(): HasOne
    {
        return $this->hasOne(PaymentReceipt::class, 'payment_id');
    }

    public static function generateReceiptNumber(string $prefix = 'RCPT'): string
    {
        $date = now();
        $month = $date->format('Ym');
        $sequence = static::whereYear('created_at', $date->year)
            ->whereMonth('created_at', $date->month)
            ->count() + 1;

        return sprintf('%s-%s-%04d', $prefix, $month, $sequence);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->payment_status) {
            'pending' => 'bg-warning text-dark',
            'paid' => 'bg-success',
            'partially_paid' => 'bg-info',
            'overdue' => 'bg-danger',
            'cancelled' => 'bg-dark',
            default => 'bg-secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'pending' => 'Pending',
            'paid' => 'Paid',
            'partially_paid' => 'Partially Paid',
            'overdue' => 'Overdue',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->payment_status),
        };
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash' => 'Cash',
            'gcash' => 'GCash',
            'maya' => 'Maya',
            'bank_transfer' => 'Bank Transfer',
            default => ucfirst(str_replace('_', ' ', $this->payment_method)),
        };
    }
}
