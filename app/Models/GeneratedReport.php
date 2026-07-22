<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratedReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'report_name',
        'report_type',
        'category',
        'village_id',
        'format',
        'file_path',
        'file_size',
        'filters',
        'is_scheduled',
        'schedule_frequency',
        'next_run_at',
        'last_run_at',
        'is_active',
    ];

    protected $casts = [
        'filters' => 'array',
        'is_scheduled' => 'boolean',
        'is_active' => 'boolean',
        'next_run_at' => 'datetime',
        'last_run_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function getFileSizeFormattedAttribute(): string
    {
        if (!$this->file_size) return 'N/A';
        $bytes = (int) $this->file_size;
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }
}
