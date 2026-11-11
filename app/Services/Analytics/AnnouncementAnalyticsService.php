<?php

namespace App\Services\Analytics;

use App\Models\Announcement;
use App\Models\Village;

class AnnouncementAnalyticsService
{
    public function getVillageStats(int $villageId): array
    {
        $total = Announcement::forVillage($villageId)->count();
        $published = Announcement::forVillage($villageId)->where('status', 'published')->count();
        $draft = Announcement::forVillage($villageId)->where('status', 'draft')->count();
        $archived = Announcement::forVillage($villageId)->where('status', 'archived')->count();
        $recentlyPublished = Announcement::forVillage($villageId)
            ->where('status', 'published')
            ->where('created_at', '>=', now()->subWeek())
            ->count();

        return [
            'total' => $total,
            'published' => $published,
            'draft' => $draft,
            'archived' => $archived,
            'recently_published' => $recentlyPublished,
        ];
    }

    public function getSystemStats(): array
    {
        $total = Announcement::count();
        $published = Announcement::where('status', 'published')->count();
        $draft = Announcement::where('status', 'draft')->count();

        return [
            'total' => $total,
            'published' => $published,
            'draft' => $draft,
        ];
    }

    public function getVillageComparison(): array
    {
        $villages = Village::where('status', 'active')->get();

        return $villages->map(function ($village) {
            $stats = $this->getVillageStats($village->id);
            return [
                'village' => $village,
                'total' => $stats['total'],
                'published' => $stats['published'],
            ];
        })->toArray();
    }
}
