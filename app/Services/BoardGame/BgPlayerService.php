<?php

namespace App\Services\BoardGame;

use App\Http\Resources\BoardGame\Player\BgPlayerLayoutResource;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGamePlayer;
use App\Services\Cache\BoardGame\BgPlayerCacheService;
use App\Services\Cache\BoardGame\BgPlayerGameCacheService;
use App\Services\Entity\EntityService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class BgPlayerService
{
    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            BoardGamePlayer::class,
            BoardGamePlayer::CACHE_SERVICE,
            BoardGamePlayer::DETAIL_RESOURCE,
            $id,
            [
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

    public static function getCurrent($bgSlug)
    {
        if (!$bgSlug) return null;

        $user = Auth::user();

        if (!$user) return null;

        $cacheKey = BgPlayerCacheService::DETAIL_PREFIX . '_' . $bgSlug . '_' . $user->id;
        $cache = Cache::get($cacheKey);

        if ($cache) {
            return $cache;
        } else {
            $boardGame = BoardGame::findBySlug($bgSlug)->first();
            if (!$boardGame) return null;

            $boardGame->load([
                'players' => fn($q) => $q->where('user_id', $user->id),
                'players.user',
                'players.user.avatar',
                'players.user.additionalFields',
                'players.positions' => function ($query) use ($boardGame) {
                    $query
                        ->where('board_game_id', $boardGame->id)
                        ->orderByDesc('updated_at')
                        ->orderByDesc('id');
                },
                'players.mainTimers' => function ($query) use ($boardGame) {
                    $query
                        ->where('board_game_id', $boardGame->id)
                        ->orderByDesc('id');
                },
                'players.mainTimers.playerTimer',
                'players.currentGames' => function ($query) use ($boardGame) {
                    $query->where('board_game_id', $boardGame->id);
                },
            ]);

            $player = $boardGame->players->first();

            if (!$player) return null;

            return Cache::remember($cacheKey, BgPlayerGameCacheService::TIME, function () use ($player) {
                return BgPlayerLayoutResource::make($player);
            });
        }
    }
}
