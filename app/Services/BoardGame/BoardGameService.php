<?php

namespace App\Services\BoardGame;

use App\Http\Resources\BoardGame\BgLayoutResource;
use App\Models\BoardGame\BoardGame;
use App\Services\Cache\BoardGame\BoardGameCacheService;
use App\Services\Entity\EntityService;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class BoardGameService extends ServiceProvider
{
    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            BoardGame::class,
            'App\Services\Cache\BoardGame\BoardGameCacheService',
            'App\Http\Resources\Admin\BoardGame\DetailResource',
            $id,
            [
                'settings',
                'tags',
                'additionalFields',
                'media',
                'seo',
                'seo.entity',
                'seo.entity.tags',
                'menu',
                'menu.elements',
                'blocks',
            ],
            $forceRefresh,
            $withTrashed,
        );
    }

    public static function getBySlug($slug)
    {
        $cacheKey = BoardGameCacheService::DETAIL_PREFIX . '_' . $slug;
        $cache = Cache::get($cacheKey);

        if ($cache) {
            return $cache;
        } else {
            $time = BoardGameCacheService::TIME;

            $boardGame = BoardGame::query()
                ->with(['settings', 'media', 'seo', 'seo.entity', 'seo.entity.tags', 'blocks'])
                ->findBySlug($slug)
                ->active()
                ->first();

            if ($boardGame->started_at && $boardGame->started_at >= Carbon::now()) {
                $time = $boardGame->started_at;
            } else if ($boardGame->ended_at && $boardGame->ended_at > Carbon::now()) {
                $time = $boardGame->ended_at;
            }

            return Cache::remember($cacheKey, $time, function () use ($boardGame) {
                return BgLayoutResource::make($boardGame);
            });
        }
    }
}
