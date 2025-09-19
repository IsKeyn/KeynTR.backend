<?php

namespace App\Services;

use App\Jobs\BoardGame\BoardGameShortCacheClear;
use App\Models\BoardGame\BoardGame;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class CacheService extends ServiceProvider
{
    public static function forgetEntityCache(
        $entityName,
        $entityFolder = null,
        $entity = null,
        $slug = null,
        $date = null
    ): void
    {
        if ($entityFolder === 'BoardGame' && $entityName === 'BoardGame' && $slug) {
            Cache::forget('board_game_' . $slug . '_short_cache');

            self::setJobForDelete($entityName, $entityFolder, $slug, $date, $entity->ended_at);
        }

        if ($entityFolder === 'BoardGame' && $entityName === 'BoardGamePlayer') {
            $slug = BoardGame::findById($entity->board_game_id)->value('slug');

            Cache::forget('board_game_' . $slug . '_player_' . $entity->user_id . '_cache');
            Cache::forget('board_game_' . $slug . '_player_list_cache');
        }
    }

    public static function setJobForDelete(
        $entityName,
        $entityFolder = null,
        $slug = null,
        $date = null,
        $oldDate = null
    ): void
    {
        if ($entityFolder === 'BoardGame' && $entityName === 'BoardGame' && $slug && $date && $oldDate !== $date) {
            $clearCacheDate = Carbon::create($date);
            BoardGameShortCacheClear::dispatch($slug)->delay($clearCacheDate);
        }
    }
}
