<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'icon',
        'color',
        'action_url',
        'read_at',
        'archived_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    /**
     * Archive notifications older than one month and permanently delete
     * those older than two months.
     *
     * @return array{0: int, 1: int} [archived, deleted]
     */
    public static function prune(): array
    {
        $deleted = static::where('created_at', '<', now()->subMonths(2))->delete();

        $archived = static::whereNull('archived_at')
            ->where('created_at', '<', now()->subMonth())
            ->update(['archived_at' => now()]);

        return [$archived, $deleted];
    }

    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }
}
