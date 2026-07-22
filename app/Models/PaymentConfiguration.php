<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentConfiguration extends Model
{
    protected $table = 'payment_configurations';

    protected $fillable = [
        'village_id',
        'payment_enabled',
        'payment_manager_role',
        'penalty_rate',
        'grace_period_days',
        'billing_prefix',
        'receipt_prefix',
        'permit_prefix',
        'payment_notes',
        'monthly_due_day',
        'annual_due_month',
        'annual_due_day',
    ];

    protected $casts = [
        'payment_enabled' => 'boolean',
        'penalty_rate' => 'decimal:2',
        'monthly_due_day' => 'integer',
        'annual_due_month' => 'integer',
        'annual_due_day' => 'integer',
    ];

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function paymentTypes(): HasMany
    {
        return $this->hasMany(PaymentType::class, 'village_id', 'village_id');
    }

    public function householdAmounts(): HasMany
    {
        return $this->hasMany(\App\Models\HouseholdPaymentAmount::class, 'village_id', 'village_id');
    }
}
