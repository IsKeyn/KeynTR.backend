<?php

namespace App\Services\BoardGame;

use App\Http\Resources\BoardGame\Player\BgPlayerLayoutResource;
use App\Http\Resources\BoardGame\Player\BgPlayerWithInventoryResource;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGamePlayer;
use App\Services\Cache\BoardGame\BgPlayerCacheService;
use App\Services\Cache\BoardGame\BgPlayerGameCacheService;
use App\Services\Entity\EntityService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

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

    public static function getPlayerWithInventory($player)
    {
        $withInventoryCacheKey = BgPlayerCacheService::DETAIL_PREFIX . '_' . $player->id . '_with_inventory';

        return Cache::remember($withInventoryCacheKey, BgPlayerCacheService::TIME, function () use ($player) {
            $player->load([
                'inventory' => function ($query) {
                    $query->active()->orderBy('updated_at', 'desc');
                },
                'inventory.itemBind.item',
                'inventory.itemBind.item.titleImage',
                'inventory.itemBind.item.sound',
                'inventory.itemBind.item.authorUser',
                'statusEffects' => function ($query) {
                    $query->active()->orderBy('updated_at', 'desc');
                },
                'statusEffects.statusEffectBind.statusEffect.titleImage',
            ]);

            return BgPlayerWithInventoryResource::make($player);
        });
    }

    public static function getPlayerWithInventoryById($playerId)
    {
        $withInventoryCacheKey = BgPlayerCacheService::DETAIL_PREFIX . '_' . $playerId . '_with_inventory_obs';

        return Cache::remember($withInventoryCacheKey, BgPlayerCacheService::TIME, function () use ($playerId) {
            if (!$playerId) {
                return response()
                    ->json(['error' => __('notReceived.not_received_id')])
                    ->setStatusCode(Response::HTTP_BAD_REQUEST);
            }

            $player = BoardGamePlayer::query()
                ->findById($playerId)
                ->with([
                    'user',
                    'positions' => function ($query) {
                        $query->orderBy('id', 'desc');
                    },
                    'statusEffects' => function ($query) {
                        $query->active()->orderBy('updated_at', 'desc');
                    },
                    'statusEffects.statusEffectBind.statusEffect.titleImage',
                    'inventory' => function ($query) {
                        $query->active()->where('has_used', false)->orderBy('created_at', 'desc');
                    },
                    'inventory.itemBind.item',
                    'inventory.itemBind.item.titleImage',
                    'currentGames',
                    'currentGames.game',
                    'currentGames.game.game',
                    'currentGames.game.game.titleImage',
                ])
                ->first();

            if (!$player) {
                return response()
                    ->json(['error' => __('boardGame.player.not_found')])
                    ->setStatusCode(Response::HTTP_BAD_REQUEST);
            }

            return BgPlayerWithInventoryResource::make($player);
        });
    }

    protected function distance($value): void
    {
        if (!$value) {
            return;
        }

        $table = self::TABLE_NAME;

        // Исправление: проверяем тип данных и получаем значения корректно
        if (is_array($value)) {
            $currentUserId = $value['user_id'] ?? null;
            $minDistance = $value['min_distance'] ?? null;
            $maxDistance = $value['max_distance'] ?? null;
        } else {
            // Если это объект (stdClass)
            $currentUserId = $value->user_id ?? null;
            $minDistance = $value->min_distance ?? null;
            $maxDistance = $value->max_distance ?? null;
        }

        if (!$currentUserId || ($minDistance === null && $maxDistance === null)) {
            return;
        }

        // Подзапрос для получения позиции текущего игрока
        $currentPlayerPositionSubquery = BoardGamePlayerPosition::select('position')
            ->where('user_id', $currentUserId)
            ->whereColumn('board_game_id', $table . '.board_game_id')
            ->orderByDesc('id')
            ->limit(1);

        $currentPositionSql = "COALESCE(({$currentPlayerPositionSubquery->toSql()}), 0)";
        $currentPositionBindings = $currentPlayerPositionSubquery->getBindings();

        // Подзапрос для получения позиции каждого игрока
        $playerPositionSubquery = BoardGamePlayerPosition::select('position')
            ->whereColumn('user_id', $table . '.user_id')
            ->whereColumn('board_game_id', $table . '.board_game_id')
            ->orderByDesc('id')
            ->limit(1);

        $playerPositionSql = "COALESCE(({$playerPositionSubquery->toSql()}), 0)";
        $playerPositionBindings = $playerPositionSubquery->getBindings();

        // Формируем условие расстояния
        $distanceExpression = "ABS({$playerPositionSql} - {$currentPositionSql})";
        $bindings = array_merge($playerPositionBindings, $currentPositionBindings);

        $conditions = [];

        if ($maxDistance !== null) {
            $conditions[] = "{$distanceExpression} <= ?";
            $bindings[] = (int)$maxDistance;
        }

        if ($minDistance !== null) {
            $conditions[] = "{$distanceExpression} >= ?";
            $bindings[] = (int)$minDistance;
        }

        if (!empty($conditions)) {
            $this->query->whereRaw(implode(' AND ', $conditions), $bindings);
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
