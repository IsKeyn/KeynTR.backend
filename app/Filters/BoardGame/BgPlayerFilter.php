<?php
namespace App\Filters\BoardGame;

use App\Filters\Concerns\HasFilters;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\BoardGamePlayerPosition;
use App\Models\BoardGame\PlayerInteractions;
use App\Models\User;
use App\Models\ViewsCount;
use App\Models\VotesCount;
use App\Services\TwitchService;
use Illuminate\Database\Eloquent\Builder;

class BgPlayerFilter
{
    use HasFilters {
        apply as traitApply;
    }

    private const MODEL = BoardGamePlayer::class;
    private const TABLE_NAME = BoardGamePlayer::TABLE_NAME;

    public function apply(Builder $query): Builder
    {
        $filters = $this->filters();
        $onlyInactive = isset($filters['only_inactive'])
            && filter_var($filters['only_inactive'], FILTER_VALIDATE_BOOLEAN);

        if ($onlyInactive) {
            $query->where('active', false);
        } else {
            $query->where('active', true);
        }

        return $this->traitApply($query);
    }

    protected function search($value): void
    {
        if ($value) {
            $this->query->whereHas('user', function (Builder $query) use ($value) {
                $query->where('public_name', 'like', '%' . $value . '%');
            });
        }
    }

    protected function exceptPlayer($value): void
    {
        if ($value) {
            $this->query->whereNotIn('id', $value);
        }
    }

    protected function twitchStreamOnline($value): void
    {
        if ($value) {
            $twitchService = app(TwitchService::class);
            $result = $twitchService->streamersLive();
            $this->query->whereIn('user_id', array_column($result, 'site_user_id'));
        }
    }

    protected function notActive($value): void
    {
        if ($value) {
            $twitchService = app(TwitchService::class);
            $result = $twitchService->streamersLive();
            $this->query->whereIn('user_id', array_column($result, 'site_user_id'));
        }
    }

    protected function distance($value): void
    {
        if (!$value) {
            return;
        }

        $table = self::TABLE_NAME;
        $currentUserId = is_array($value) ? ($value['user_id'] ?? null) : ($value->user_id ?? null);
        $minDistance = is_array($value) ? ($value['min_distance'] ?? null) : ($value->min_distance ?? null);
        $maxDistance = is_array($value) ? ($value['max_distance'] ?? null) : ($value->max_distance ?? null);

        if (!$currentUserId || ($minDistance === null && $maxDistance === null)) {
            return;
        }

        // Подзапрос для получения позиции текущего игрока
        $currentPlayerPositionSubquery = BoardGamePlayerPosition::select('position')
            ->where('user_id', $currentUserId)
            ->whereColumn('board_game_id', $table . '.board_game_id')
            ->orderByDesc('id')
            ->limit(1);

        $currentPositionSql = "COALESCE(({$currentPlayerPositionSubquery->toSql()}), 1)";
        $currentPositionBindings = $currentPlayerPositionSubquery->getBindings();

        // Подзапрос для получения позиции каждого игрока
        $playerPositionSubquery = BoardGamePlayerPosition::select('position')
            ->whereColumn('user_id', $table . '.user_id')
            ->whereColumn('board_game_id', $table . '.board_game_id')
            ->orderByDesc('id')
            ->limit(1);

        $playerPositionSql = "COALESCE(({$playerPositionSubquery->toSql()}), 1)";
        $playerPositionBindings = $playerPositionSubquery->getBindings();

        // Формируем условие расстояния
        $distanceExpression = "ABS({$playerPositionSql} - {$currentPositionSql})";
        $bindings = array_merge($playerPositionBindings, $currentPositionBindings);

        $conditions = [];

        if ($maxDistance !== null) {
            $conditions[] = "{$distanceExpression} <= ?";
            $bindings[] = $maxDistance;
        }

        if ($minDistance !== null) {
            $conditions[] = "{$distanceExpression} >= ?";
            $bindings[] = $minDistance;
        }

        if (!empty($conditions)) {
            $this->query->whereRaw(implode(' AND ', $conditions), $bindings);
        }
    }

    protected function nearestOnly($value): void
    {
        if (!$value) {
            return;
        }

        $table = self::TABLE_NAME;
        $currentUserId  = is_array($value) ? ($value['user_id'] ?? null)        : ($value->user_id ?? null);
        $targetPosition = is_array($value) ? ($value['target_position'] ?? null): ($value->target_position ?? null);
        $bgSlug         = is_array($value) ? ($value['bg_slug'] ?? null)        : ($value->bg_slug ?? null);

        $boardGameId = BoardGame::query()->findBySlug($bgSlug)->value('id');

        if ($targetPosition === null && !$currentUserId) {
            return;
        }

        // 1. Определяем целевую позицию
        if ($targetPosition !== null) {
            $target = (int) $targetPosition;
        } else {
            $target = (int) (BoardGamePlayerPosition::where('user_id', $currentUserId)
                    ->where('board_game_id', $boardGameId)
                    ->orderByDesc('id')
                    ->value('position') ?? 1);
        }

        // 2. Получаем ПОСЛЕДНИЕ позиции всех игроков этой настолки
        // Ключевое исправление: MAX(id), а не MAX(position)!
        // Это даёт запись с самым свежим id для каждого игрока.
        $lastPositions = BoardGamePlayerPosition::query()
            ->select('user_id', 'position')
            ->where('board_game_id', $boardGameId)
            ->whereIn('id', function ($q) use ($boardGameId) {
                $q->selectRaw('MAX(id)')
                    ->from('board_game_player_positions')
                    ->where('board_game_id', $boardGameId)
                    ->groupBy('user_id');
            })
            ->get()
            ->pluck('position', 'user_id')
            ->map(fn($p) => (int) $p)       // приводим к int для корректной математики
            ->toArray();

        // 3. Получаем ID всех активных игроков настолки
        // (в т.ч. тех, у кого ещё нет записей в BoardGamePlayerPosition — они на позиции 1)
        $allPlayerUserIds = BoardGamePlayer::where('board_game_id', $boardGameId)
            ->where('active', true)
            ->pluck('user_id')
            ->toArray();

        if (empty($allPlayerUserIds)) {
            $this->query->whereRaw('1 = 0');
            return;
        }

        // 4. Считаем расстояния
        $distances = [];
        foreach ($allPlayerUserIds as $userId) {
            // Текущий игрок не может быть целью для самого себя
            if ($userId == $currentUserId) {
                continue;
            }

            $position = $lastPositions[$userId] ?? 1; // дефолтная позиция, если записей нет
            $distances[$userId] = abs($position - $target);
        }

        if (empty($distances)) {
            $this->query->whereRaw('1 = 0');
            return;
        }

        // 5. Находим минимальное расстояние
        $minDistance = min($distances);

        // 6. Собираем ВСЕХ игроков с этим расстоянием (не одного!)
        $nearestUserIds = array_keys(array_filter($distances, fn($d) => $d === $minDistance));

        // 7. Фильтруем основной запрос
        $this->query->whereIn($table . '.user_id', $nearestUserIds);
    }

    protected function notPlayBattleForPoints($value): void
    {
        if ($value) {
            $currentUserId = is_array($value) ? ($value['user_id'] ?? null) : ($value->user_id ?? null);
            $bgSlug = is_array($value) ? ($value['bg_slug'] ?? null) : ($value->bg_slug ?? null);

            $boardGameId = BoardGame::query()->findBySlug($bgSlug)->value('id');

            $playerInteractions = PlayerInteractions::query()
                ->where('board_game_id', $boardGameId)
                ->where('created_by', $currentUserId)
                ->where('status', PlayerInteractions::I_WIN)
                ->orWhere('status', PlayerInteractions::I_LOSE)
                ->where('type', 'battleForPoints')
                ->select('with_player')
                ->get();

            $arWithPlayers = [];

            foreach ($playerInteractions as $interaction) {
                $arWithPlayers[] = $interaction->with_player;
            }

            $this->query->whereNotIn('user_id', $arWithPlayers);
        }
    }

    protected function notInvitedToCoop($value): void
    {
        if ($value) {
            $currentUserId = is_array($value) ? ($value['user_id'] ?? null) : ($value->user_id ?? null);
            $bgSlug = is_array($value) ? ($value['bg_slug'] ?? null) : ($value->bg_slug ?? null);

            $boardGameId = BoardGame::query()->findBySlug($bgSlug)->value('id');

            $playerInteractions = PlayerInteractions::query()
                ->where('board_game_id', $boardGameId)
                ->where('created_by', $currentUserId)
                ->where('status', PlayerInteractions::COOP_FINISH)
                ->where('type', 'inviteToCoop')
                ->select('with_player')
                ->get();

            $arWithPlayers = [];

            foreach ($playerInteractions as $interaction) {
                $arWithPlayers[] = $interaction->with_player;
            }

            $this->query->whereNotIn('user_id', $arWithPlayers);
        }
    }

    protected function streak($value): void
    {
        if ($value) {
            $type = is_array($value) ? ($value['type'] ?? null) : ($value->type ?? null);
            $typeValue = is_array($value) ? ($value['value'] ?? null) : ($value->value ?? null);

            if (str_contains($type, 'moreThen')) {
                $this->query->where('streak', '>', $typeValue);
            } elseif (str_contains($type, 'lessThen')) {
                $this->query->where('streak', '<', $typeValue);
            } elseif (str_contains($type, 'moreThenOrEquals')) {
                $this->query->where('streak', '>=', $typeValue);
            } elseif (str_contains($type, 'lessThenOrEquals')) {
                $this->query->where('streak', '<=', $typeValue);
            } else {
                $this->query->where('streak', $typeValue);
            }
        }
    }

    protected function sort($value): void
    {
       if ($value) {
           switch ($value->field) {
               case 'name':
                   $this->query->orderBy(
                       User::select('public_name')
                           ->whereColumn('users.id', self::TABLE_NAME . '.user_id')
                           ->limit(1),
                       $value->sort ?? 'asc'
                   );
                   break;
               case 'likes':
                   $this->query
                       ->with('likes')
                       ->orderBy(
                           VotesCount::select('value')
                               ->whereColumn('entity_id', self::TABLE_NAME . '.id')
                               ->where('entity_type', self::MODEL)
                               ->limit(1),
                           $value->sort
                       );
                   break;
               case 'views':
                   $this->query
                       ->with('views')
                       ->orderBy(
                           ViewsCount::select('value')
                               ->whereColumn('entity_id', self::TABLE_NAME . '.id')
                               ->where('entity_type', self::MODEL)
                               ->limit(1),
                           $value->sort
                       );
                   break;
               case 'date':
                   $this->query
                       ->with('dates')
                       ->withAggregate('dates', 'date')
                       ->orderBy('dates_date', $value->sort);
                   break;

               case 'sort':
                   $this->query->orderByRaw('sort IS NULL, sort ' . $value->sort);
                   break;

               case 'full_points':
                   // Создаем подзапрос, имитирующий ->first()
                   $table = self::TABLE_NAME;

                   $positionSubquery = BoardGamePlayerPosition::select('position')
                       ->whereColumn('board_game_id', $table . '.id')
                       ->limit(1);

                   $subquerySql = $positionSubquery->toSql();

                   // Формируем выражение: points + COALESCE(подзапрос, 0)
                   $orderByExpression = "({$table}.points + COALESCE(({$subquerySql}), 0))";

                   $direction = (isset($value->sort) && strtolower($value->sort) === 'asc') ? 'ASC' : 'DESC';

                    $this->query->orderByRaw(
                        "{$orderByExpression} {$direction}",
                        $positionSubquery->getBindings()
                    );
                    break;

               case 'position':
                   $table = self::TABLE_NAME;

                   $positionSubquery = BoardGamePlayerPosition::select('position')
                       ->whereColumn('user_id', $table . '.user_id')
                       ->whereColumn('board_game_id', $table . '.board_game_id')
                       ->orderByDesc('id')
                       ->limit(1);

                   $subquerySql = $positionSubquery->toSql();

                   $direction = (isset($value->sort) && strtolower($value->sort) === 'desc') ? 'DESC' : 'ASC';

                   $this->query->orderByRaw(
                       "COALESCE(({$subquerySql}), 1) {$direction}",
                       $positionSubquery->getBindings()
                   );
                   break;

               default:
                   $this->query->orderBy($value->field, $value->sort);
                   break;
           }

           $field = strtolower($value->field);
           if ($field !== 'id') {
               $this->query->orderBy('id', 'asc');
           }
       }
    }
}
