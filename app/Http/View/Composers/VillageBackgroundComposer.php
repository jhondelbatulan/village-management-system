<?php

namespace App\Http\View\Composers;

use App\Models\Village;
use App\Models\VillagePhoto;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Supplies the rotating village photos shown behind the login and
 * password-recovery screens.
 *
 * Both sources are used so any photo added through the admin panel shows up:
 *   1. each active village's cover photo, then
 *   2. the active villages' uploaded photo gallery.
 */
class VillageBackgroundComposer
{
    /**
     * Upper bound so a login page never pulls in an unbounded number of
     * full-size photos.
     */
    private const MAX_IMAGES = 6;

    public function compose(View $view): void
    {
        $view->with('villageBackgrounds', $this->backgrounds());
    }

    /**
     * @return array<int, string>
     */
    private function backgrounds(): array
    {
        $activeVillageIds = Village::query()
            ->where('status', 'active')
            ->pluck('id');

        if ($activeVillageIds->isEmpty()) {
            return [asset(config('app_settings.assets.login_background'))];
        }

        $coverPhotos = Village::query()
            ->whereKey($activeVillageIds)
            ->whereNotNull('cover_photo')
            ->orderBy('village_name')
            ->pluck('cover_photo');

        $galleryPhotos = VillagePhoto::query()
            ->whereIn('village_id', $activeVillageIds)
            ->orderBy('sort_order')
            ->pluck('photo_path');

        $urls = $coverPhotos
            ->concat($galleryPhotos)
            ->map(fn ($path) => is_string($path) ? trim($path) : '')
            ->filter(fn (string $path) => $path !== '')
            ->unique()
            ->filter(fn (string $path) => $this->exists($path))
            ->take(self::MAX_IMAGES)
            ->map(fn (string $path) => asset('storage/' . $path))
            ->values()
            ->all();

        // Never render a bare page: fall back to the branded default art.
        return $urls !== [] ? $urls : [asset(config('app_settings.assets.login_background'))];
    }

    private function exists(string $path): bool
    {
        try {
            return Storage::disk('public')->exists($path);
        } catch (\Throwable) {
            return false;
        }
    }
}
