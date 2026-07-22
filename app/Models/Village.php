<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Village extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'village_name',
        'village_code',
        'description',
        'address',
        'contact_number',
        'email',
        'latitude',
        'longitude',
        'total_households',
        'total_population',
        'total_area',
        'land_area_sq_km',
        'established_date',
        'logo',
        'cover_photo',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'total_households' => 'integer',
            'total_population' => 'integer',
            'total_area' => 'decimal:2',
            'land_area_sq_km' => 'decimal:4',
            'established_date' => 'date',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function households(): HasMany
    {
        return $this->hasMany(Household::class);
    }

    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class);
    }

    public function officers(): HasMany
    {
        return $this->hasMany(VillageOfficer::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(VillageDocument::class);
    }

    public function paymentConfiguration()
    {
        return $this->hasOne(PaymentConfiguration::class);
    }

    public function paymentTypes(): HasMany
    {
        return $this->hasMany(PaymentType::class);
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

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(VillagePhoto::class)->orderBy('sort_order');
    }

    public function isPaymentEnabled(): bool
    {
        return $this->paymentConfiguration?->payment_enabled ?? false;
    }

    public function getLogoUrlAttribute(): string
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }
        return asset(config('app_settings.assets.default_village_logo'));
    }

    public function getCoverPhotoUrlAttribute(): string
    {
        if ($this->cover_photo) {
            return asset('storage/' . $this->cover_photo);
        }
        return asset(config('app_settings.assets.default_village_cover'));
    }

    public function recalculateCounts(): void
    {
        $this->update([
            'total_households' => $this->households()->count(),
            'total_population' => $this->residents()->where('status', 'active')->count(),
        ]);
    }
}
