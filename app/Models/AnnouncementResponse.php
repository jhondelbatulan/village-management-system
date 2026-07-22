<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnouncementResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'announcement_id',
        'resident_id',
        'household_id',
        'response',
        'note',
    ];

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }
}
