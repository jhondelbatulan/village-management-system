<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VillagePhoto extends Model
{
    protected $fillable = [
        'village_id',
        'photo_path',
        'caption',
        'sort_order',
    ];

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function getPhotoUrlAttribute(): string
    {
        return $this->photo_path
            ? asset('storage/' . $this->photo_path)
            : asset('assets/images/default-village.svg');
    }
}
