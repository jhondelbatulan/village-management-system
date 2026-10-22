<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Billing;

class Announcement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'village_id',
        'title',
        'category',
        'announcement_type',
        'description',
        'cover_image',
        'attachment',
        'start_date',
        'expiration_date',
        'status',
        'is_pinned',
        'audience_type',
        'target_villages',
        'target_audience',
        'visible_roles',
        'requires_attendance',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'expiration_date' => 'date',
            'is_pinned' => 'boolean',
            'requires_attendance' => 'boolean',
            'target_villages' => 'array',
            'target_audience' => 'array',
            'visible_roles' => 'array',
        ];
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Only the creator can edit/manage/delete this announcement. Legacy rows
     * without a creator stay manageable by super admins.
     */
    public function isManagedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->created_by !== null && (int) $this->created_by === (int) $user->id) {
            return true;
        }

        return $this->created_by === null && $user->role?->role_slug === 'super_admin';
    }

    public function responses(): HasMany
    {
        return $this->hasMany(AnnouncementResponse::class);
    }

    public function getCoverImageUrlAttribute(): string
    {
        if ($this->cover_image) {
            return asset('storage/' . $this->cover_image);
        }
        return asset('assets/images/default-announcement.png');
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        if ($this->attachment) {
            return asset('storage/' . $this->attachment);
        }
        return null;
    }

    public function isExpired(): bool
    {
        return $this->expiration_date && $this->expiration_date->isPast();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('expiration_date')
                  ->orWhere('expiration_date', '>=', now());
            });
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeVisibleToRole($query, ?string $role, ?int $userId = null)
    {
        if ($role === null || $role === 'super_admin') {
            return $query;
        }

        return $query->where(function ($q) use ($role, $userId) {
            $q->whereNull('visible_roles')
              ->orWhereJsonContains('visible_roles', $role);

            if ($userId !== null) {
                $q->orWhere('created_by', $userId);
            }
        });
    }

    public function isVisibleToRole(?string $role, ?int $userId = null): bool
    {
        if ($userId !== null && $this->created_by === $userId) {
            return true;
        }

        if ($role === null || $role === 'super_admin') {
            return true;
        }

        if (empty($this->visible_roles)) {
            return true;
        }

        return in_array($role, $this->visible_roles, true);
    }

    public function getTypeBadgeClass(): string
    {
        return match($this->announcement_type ?? 'general') {
            'payment_reminder' => 'bg-warning text-dark',
            'emergency' => 'bg-danger',
            'meeting' => 'bg-info text-dark',
            'community' => 'bg-primary',
            'health' => 'bg-success',
            'disaster' => 'bg-danger',
            'training' => 'bg-secondary',
            'sports' => 'bg-info',
            'attendance_required' => 'bg-purple text-white',
            'others' => 'bg-light text-dark',
            default => 'bg-primary',
        };
    }

    public function getTypeLabel(): string
    {
        return match($this->announcement_type ?? 'general') {
            'payment_reminder' => 'Payment Reminder',
            'emergency' => 'Emergency',
            'meeting' => 'Meeting',
            'community' => 'Community',
            'health' => 'Health',
            'disaster' => 'Disaster',
            'training' => 'Training',
            'sports' => 'Sports',
            'attendance_required' => 'Attendance Required',
            'others' => 'Others',
            default => 'General',
        };
    }

    public function isVisibleToResident(int $villageId, ?int $householdId = null): bool
    {
        if ($this->village_id && $this->village_id != $villageId && !in_array($this->village_id, $this->target_villages ?? [])) {
            return false;
        }

        if (($this->audience_type ?? 'all_residents') === 'all_residents') {
            return true;
        }

        if (($this->audience_type ?? 'all_residents') === 'entire_village') {
            return true;
        }

        if (($this->audience_type ?? 'all_residents') === 'selected_households' && $householdId) {
            $targetHouseholds = $this->target_audience['household_ids'] ?? [];
            return in_array($householdId, $targetHouseholds);
        }

        if (($this->audience_type ?? 'all_residents') === 'specific_block' && $householdId) {
            $targetBlock = $this->target_audience['block_number'] ?? null;
            if ($targetBlock) {
                $household = Household::find($householdId);
                return $household && $household->block_number === $targetBlock;
            }
        }

        if (($this->audience_type ?? 'all_residents') === 'village_officers') {
            return VillageOfficer::where('village_id', $villageId)
                ->where('resident_id', auth()->user()->household?->residents()->first()?->id)
                ->exists();
        }

        if (($this->audience_type ?? 'all_residents') === 'outstanding_payments') {
            return Billing::where('village_id', $villageId)
                ->where('household_id', $householdId)
                ->whereIn('status', ['pending', 'overdue', 'partially_paid'])
                ->exists();
        }

        if (($this->audience_type ?? 'all_residents') === 'fully_paid') {
            $hasOutstanding = Billing::where('village_id', $villageId)
                ->where('household_id', $householdId)
                ->whereIn('status', ['pending', 'overdue', 'partially_paid'])
                ->exists();
            return !$hasOutstanding;
        }

        return true;
    }
}
