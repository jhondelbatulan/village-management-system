<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'village_id',
        'title',
        'category',
        'description',
        'venue',
        'event_date',
        'start_time',
        'end_time',
        'max_participants',
        'cover_image',
        'attachment',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'max_participants' => 'integer',
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

    public function responses(): HasMany
    {
        return $this->hasMany(EventResponse::class);
    }

    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class);
    }

    public function getCoverImageUrlAttribute(): string
    {
        if ($this->cover_image) {
            return asset('storage/' . $this->cover_image);
        }
        return asset('assets/images/default-event.png');
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        if ($this->attachment) {
            return asset('storage/' . $this->attachment);
        }
        return null;
    }

    public function getGoingCountAttribute(): int
    {
        return $this->responses()->where('response', 'going')->count();
    }

    public function getMaybeCountAttribute(): int
    {
        return $this->responses()->where('response', 'maybe')->count();
    }

    public function getNotGoingCountAttribute(): int
    {
        return $this->responses()->where('response', 'not_going')->count();
    }

    public function getParticipantCountAttribute(): int
    {
        return $this->participations()->count();
    }

    public function getAttendedCountAttribute(): int
    {
        return $this->participations()->where('status', 'present')->count();
    }
}
