<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role',
        'village_id',
        'action',
        'module',
        'subject_type',
        'subject_id',
        'description',
        'properties',
        'ip_address',
        'user_agent',
        'browser',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function getBrowserNameAttribute(): string
    {
        if (!$this->user_agent) return 'Unknown';

        $ua = strtolower($this->user_agent);
        return match(true) {
            str_contains($ua, 'firefox') => 'Firefox',
            str_contains($ua, 'edg') => 'Edge',
            str_contains($ua, 'chrome') => 'Chrome',
            str_contains($ua, 'safari') => 'Safari',
            str_contains($ua, 'opera') || str_contains($ua, 'opr') => 'Opera',
            default => 'Other',
        };
    }
}
