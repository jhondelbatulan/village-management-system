<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Household extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'village_id',
        'household_number',
        'block_number',
        'lot_number',
        'household_head_id',
        'household_head_name',
        'purok',
        'address',
        'latitude',
        'longitude',
        'contact_number',
        'household_type',
        'house_ownership',
        'house_status',
        'registration_date',
        'status',
        'approval_status',
        'submitted_by',
        'approved_by',
        'approved_at',
        'remarks',
        'admin_approved_by',
        'admin_approved_at',
        'payment_behavior',
        'payment_reliability_score',
        'prediction_confidence',
        'prediction_reasons',
        'last_prediction_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'registration_date' => 'date',
            'approved_at' => 'datetime',
            'admin_approved_at' => 'datetime',
            'last_prediction_at' => 'datetime',
            'prediction_reasons' => 'array',
            'payment_reliability_score' => 'decimal:2',
            'prediction_confidence' => 'decimal:2',
        ];
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function householdHead(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'household_head_id');
    }

    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function adminApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_approved_by');
    }

    public function billings(): HasMany
    {
        return $this->hasMany(Billing::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(HouseholdPayment::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(PaymentReceipt::class);
    }

    public function paymentAmounts(): HasMany
    {
        return $this->hasMany(HouseholdPaymentAmount::class);
    }

    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class);
    }

    public function getOutstandingBalanceAttribute(): float
    {
        $billings = $this->billings()
            ->whereIn('status', ['pending', 'overdue', 'partially_paid'])
            ->get();

        $total = 0.0;
        foreach ($billings as $billing) {
            $paid = \App\Models\HouseholdPayment::where('billing_id', $billing->id)
                ->where('payment_status', '!=', 'cancelled')
                ->sum('total_paid');
            $total += max(0, (float) $billing->total_amount - (float) $paid);
        }

        return round($total, 2);
    }

    public function getPaidTotalAttribute(): float
    {
        return (float) $this->payments()
            ->sum('total_paid');
    }

    public function getMemberCountAttribute(): int
    {
        return $this->residents()->where('status', 'active')->count();
    }

    public static function generateHouseholdNumber(int $villageId): string
    {
        $village = Village::find($villageId);
        $prefix = $village ? strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $village->village_name), 0, 3)) : 'HH';
        $count = self::where('village_id', $villageId)->count() + 1;
        return $prefix . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}
