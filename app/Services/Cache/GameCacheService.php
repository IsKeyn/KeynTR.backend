<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;
use App\Models\Game;

class GameCacheService
{
    public const LIST_PREFIX = 'game_list_cache';
    public const DETAIL_PREFIX = 'game_detail_cache';
    public const TIME = 129600;

    public function clearAllGameCache()
    {
        self::clearGameListCache();
    }

    public function clearGameListCache()
    {
        $perPageArray = [24, 28, 96];

        foreach ($perPageArray as $perPage) {
            $lastPage = Game::query()
                ->where('show_in_list', true)
                ->active()
                ->paginate($perPage)->lastPage();

            for ($i = 1; $i <= $lastPage; $i++) {
                $cacheKey = self::LIST_PREFIX . '_' . $i . '_' . $perPage;

                Cache::forget($cacheKey);
                echo $cacheKey . ' очищен' . PHP_EOL;
            }
        }
    }

    public function clearAllDetailCache()
    {
        $data = Game::query()->get();

        foreach ($data as $element) {
            self::clearDetailCacheBySlug($element->slug);
        }
    }

    public function clearDetailCacheBySlug($slug)
    {
        $cacheKey = self::DETAIL_PREFIX . '_' . $slug;

        Cache::forget($cacheKey);

        echo $cacheKey . PHP_EOL;
    }
}
