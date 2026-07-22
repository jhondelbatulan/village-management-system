<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentReceipt extends Model
{
    protected $table = 'payment_receipts';

    protected $fillable = [
        'receipt_number',
        'payment_id',
        'billing_id',
        'household_id',
        'village_id',
        'receipt_type',
        'amount_paid',
        'payment_method',
        'received_from',
        'received_by',
        'receipt_date',
        'notes',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'receipt_date' => 'date',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(HouseholdPayment::class, 'payment_id');
    }

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

    public function receivedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
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
}
