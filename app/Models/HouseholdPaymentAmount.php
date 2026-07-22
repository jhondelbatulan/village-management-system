<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HouseholdPaymentAmount extends Model
{
    protected $table = 'household_payment_amounts';

    protected $fillable = [
        'village_id',
        'household_id',
        'payment_type_id',
        'custom_amount',
    ];

    protected $casts = [
        'custom_amount' => 'decimal:2',
    ];

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class);
    }
}
