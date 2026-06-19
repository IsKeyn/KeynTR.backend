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
use Illuminate\Support\Facades\DB;

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

        $cacheKey = BgPlayerCacheService::DETAIL_PREFIX . '_' . $bgSlug . '_' . $user->id . '_layout';
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

    public function recalculatePlaces(int $boardGameId): void
    {
        // Обязательно используем транзакцию, чтобы не получить частичное обновление
        DB::transaction(function () use ($boardGameId) {

            // 1. Получаем всех игроков игры вместе с их позициями на поле
            $players = BoardGamePlayer::query()
                ->findByBoardGame($boardGameId)
                ->with(['positions'])
                ->get();

            // 2. Разделяем игроков на активных и неактивных
            $activePlayers = $players->filter(fn($player) => $player->active)->values();
            $inactiveIds   = $players->filter(fn($player) => !$player->active)->pluck('id');

            // 3. Сортируем ТОЛЬКО активных игроков по вашей логике (очки + позиция)
            $activePlayers = $activePlayers->sortByDesc(function ($player) {
                $position = $player->position ?? 0;
                return $player->points + $position;
            })->values();

            // 4. Проставляем места активным игрокам
            $rank = 1;
            $previousScore = null;
            $actualRank = 1;

            foreach ($activePlayers as $index => $player) {
                $currentScore = $player->points + ($player->position ?? 0);

                // Если очки меньше, чем у предыдущего игрока, обновляем реальное место
                // (Это реализует логику 1, 1, 3 мест при одинаковых очках)
                if ($index > 0 && $currentScore < $previousScore) {
                    $actualRank = $rank;
                }

                BoardGamePlayer::where('id', $player->id)->update(['place' => $actualRank]);

                $previousScore = $currentScore;
                $rank++;
            }

            // 5. Неактивным игрокам массово ставим null
            if ($inactiveIds->isNotEmpty()) {
                BoardGamePlayer::whereIn('id', $inactiveIds)->update(['place' => null]);
            }
        });
    }
}
