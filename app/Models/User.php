<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'role_id',
        'village_id',
        'household_id',
        'username',
        'password',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'email',
        'contact_number',
        'profile_picture',
        'status',
        'must_setup',
        'last_login',
        'locked_at',
        'last_activity_at',
        'password_otp',
        'password_otp_expires_at',
    ];

    protected $hidden = [
        'password',
        'password_otp',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'must_setup' => 'boolean',
            'last_login' => 'datetime',
            'locked_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'password_otp_expires_at' => 'datetime',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function getFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->suffix,
        ]);

        return implode(' ', $parts) ?: $this->username;
    }

    public function getProfilePictureUrlAttribute(): string
    {
        if ($this->profile_picture) {
            return asset('storage/' . $this->profile_picture);
        }

        return asset(config('app_settings.assets.default_profile'));
    }

    public function hasRole(string $role): bool
    {
        return $this->role?->role_slug === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role?->role_slug, $roles);
    }
}
