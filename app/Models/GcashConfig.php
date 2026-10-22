<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GcashConfig extends Model
{
    protected $table = 'gcash_configs';

    protected $fillable = [
        'village_id',
        'gcash_number',
        'gcash_name',
    ];

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }
}
