<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FailedLogin extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'ip_address',
        'user_agent',
        'browser',
        'os',
        'reason',
        'is_flagged',
    ];

    protected $casts = [
        'is_flagged' => 'boolean',
    ];
}
