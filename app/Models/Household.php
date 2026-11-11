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
        return $this->hasMany(Resident::class)->where('approval_status', 'approved');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
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
            $paid = HouseholdPayment::where('billing_id', $billing->id)
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

    public function getMemberCountAttribute($value = null): int
    {
        if ($value !== null) {
            return (int) $value;
        }

        return $this->residents()->where('status', 'active')->count();
    }

    /**
     * Human readable household reference used in notifications, e.g.
     * "Blk 1, Lot 2 - Juan Dela Cruz" instead of the household number/code.
     */
    public function getNotificationLabelAttribute(): string
    {
        $parts = [];

        if (filled($this->block_number)) {
            $parts[] = "Blk {$this->block_number}";
        }

        if (filled($this->lot_number)) {
            $parts[] = "Lot {$this->lot_number}";
        }

        $location = implode(', ', $parts);
        $head = $this->household_head_name;

        if ($location !== '' && filled($head)) {
            return "{$location} - {$head}";
        }

        if ($location !== '') {
            return $location;
        }

        if (filled($head)) {
            return (string) $head;
        }

        return (string) $this->household_number;
    }

    /**
     * Human readable household label used in lists/tables, e.g. "Juan Dela Cruz".
     */
    public function getFamilyNameAttribute(): string
    {
        if (filled($this->household_head_name)) {
            return (string) $this->household_head_name;
        }

        return (string) $this->household_number;
    }

    /**
     * Human readable approval state, e.g. "Awaiting Final Approval".
     */
    public function getApprovalStatusLabelAttribute(): string
    {
        return match ($this->approval_status) {
            'approved' => 'Approved',
            'admin_approved' => 'Awaiting Final Approval',
            'pending' => 'Pending Approval',
            'rejected' => 'Rejected',
            default => 'Draft',
        };
    }

    public static function generateHouseholdNumber(int $villageId): string
    {
        $village = Village::find($villageId);
        $prefix = $village ? strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $village->village_name), 0, 3)) : 'HH';
        $count = self::withTrashed()->where('village_id', $villageId)->count() + 1;

        return $prefix.'-'.str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}
