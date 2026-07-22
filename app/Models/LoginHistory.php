<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginHistory extends Model
{
    use HasFactory;

    protected $table = 'login_history';

    protected $fillable = [
        'user_id',
        'username',
        'login_at',
        'logout_at',
        'session_duration_minutes',
        'ip_address',
        'user_agent',
        'browser',
        'os',
        'status',
        'failure_reason',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
            default => 'Other',
        };
    }

    public function getOsNameAttribute(): string
    {
        if (!$this->user_agent) return 'Unknown';
        $ua = strtolower($this->user_agent);
        return match(true) {
            str_contains($ua, 'windows') => 'Windows',
            str_contains($ua, 'mac os') => 'macOS',
            str_contains($ua, 'linux') => 'Linux',
            str_contains($ua, 'android') => 'Android',
            str_contains($ua, 'iphone') || str_contains($ua, 'ipad') => 'iOS',
            default => 'Other',
        };
    }
}
