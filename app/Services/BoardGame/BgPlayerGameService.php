<?php

namespace App\Services\BoardGame;

use App\Http\Resources\BoardGame\Board\BgPlayerInteractionResource;
use App\Http\Resources\BoardGame\GameListResource;
use App\Http\Resources\BoardGame\Games\BgGameRouletteListResource;
use App\Http\Resources\BoardGame\Player\BgPlayerWithCurrentGameResource;
use App\Models\BoardGame\BoardGameGameList;
use App\Models\BoardGame\PlayerGame;
use App\Models\BoardGame\PlayerInteractions;
use App\Models\BoardGame\StatusEffect;
use App\Models\GamingPlatform;
use App\Services\Entity\EntityService;
use App\Services\ErrorService;
use Illuminate\Support\Facades\DB;

/**
 * Данный service отвечает за работу с играми участника настольной игры/ивента
 *
 * Class BgPlayerGameService
 * @package App\Services\BoardGame
 */

class BgPlayerGameService
{
    /**
     * Получить игру по ID основной таблицы
     *
     * @param $id integer ID
     * @param false $forceRefresh Принудительный сброс кеша
     * @param false $withTrashed Извлекать записи, удаленные мягким удалением
     * @return mixed
     */
    public static function getById(
        $id,
        $forceRefresh = false,
        $withTrashed = false
    )
    {
        return EntityService::getById(
            PlayerGame::class,
            PlayerGame::CACHE_SERVICE,
            PlayerGame::DETAIL_RESOURCE,
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

    /**
     * Список игр участника
     *
     * @param String $slug
     * @return array|string[]
     */
    public function getList(String $slug)
    {
        $conditionData = PlayerGameService::checkConditions($slug);

        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        }

        $coopInteractions = PlayerInteractions::query()
            ->findByBoardGame($conditionData['boardGame']->id)
            ->where('created_by', $conditionData['user']->id)
            ->where('type', 'inviteToCoop')
            ->whereIn('status', [PlayerInteractions::STATUS_ACTIVE, PlayerInteractions::STATUS_ACCEPTED])
            ->with([
                'withPlayerData',
                'withPlayerData.avatar',
                'createdByData',
                'createdByData.avatar',
            ])
            ->active()
            ->get();

        $conditionData['player']
            ->load([
                'positions',
                'currentGames',
                'currentGames.game',
                'currentGames.game.platform',
                'currentGames.game.addedBy',
                'currentGames.game.game',
                'currentGames.game.game.dates',
                'currentGames.game.game.titleImage',
                'currentGames.game.game.cover',
                'currentGames.game.game.genres',
                'currentGames.boardGame',
                'currentGames.boardGame.settings',
                'currentGames.player',
                'currentGames.player.statusEffects' => function($query) {
                    $query->where('active', true);
                },
                'currentGames.player.statusEffects.statusEffectBind',
                'currentGames.player.statusEffects.statusEffectBind.statusEffect',
                'user',
                'user.avatar',
                'mainTimers' => function ($query) use ($conditionData) {
                    $query->where('board_game_id', $conditionData['boardGame']->id)->orderBy('id', 'desc');
                },
                'statusEffects' => function($query) {
                    $query->where('active', true);
                },
                'statusEffects.statusEffectBind',
                'statusEffects.statusEffectBind.statusEffect',
            ]);

        // Если есть текущая игра, то возвращаем её
        if ($conditionData['player']->currentGames->first()) {
            return [
                'status' => 1,
                'coopInteraction' => BgPlayerInteractionResource::collection($coopInteractions),
                'player' => BgPlayerWithCurrentGameResource::make($conditionData['player']),
            ];
        }

        $games = $this->getFilteredGameList($conditionData);

        return [
            'status' => 1,
            'coopInteraction' => BgPlayerInteractionResource::collection($coopInteractions),
            'games' => isset($games['gameList']) ? BgGameRouletteListResource::collection($games['gameList']) : null,
            'listType' => isset($games['listType']) ? $games['listType'] : null,
            'player' => BgPlayerWithCurrentGameResource::make($conditionData['player']),
        ];
    }

    /**
     * Функция отвечает за выбор игры, при крутке рулетки
     *
     * @param String $slug
     * @return GameListResource|array|string[]
     */
    public function roll(String $slug) {
        // Проверяем, что игрок может крутить рулетку игр и получаем ключевой набор данных
        $conditionData = PlayerGameService::checkConditions($slug);

        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        }

        $conditionData['boardGame']->load([
            'settings',
        ]);

        $conditionData['player']
            ->load([
                'mainTimers' => function ($query) use ($conditionData) {
                    $query->where('board_game_id', $conditionData['boardGame']->id)->orderBy('id', 'desc');
                },
                'statusEffects' => function($query) {
                    $query->active();
                },
                'statusEffects.statusEffectBind',
                'statusEffects.statusEffectBind.statusEffect',
                'currentGames',
            ]);

        $checkResult = $this->canPlayerRollGame($conditionData);

        if (isset($checkResult['status']) && $checkResult['status'] === 'error') {
            return $checkResult;
        }

        return DB::transaction(function () use ($conditionData) {
            $gameListFiltered = $this->getFilteredGameList($conditionData, true);

            if (isset($gameListFiltered['gameList']) && $gameListFiltered['gameList']->count() === 0) {
                return ErrorService::message(__('boardGame.player_game.dont_have_game_for_roll'));
            }

            $randomGame = $gameListFiltered['gameList']->random();

            if (!$randomGame) {
                return ErrorService::message(__('boardGame.player_game.choice_game_error'));
            }

            // Если у игрока есть текущая игра, отмечаем её как рерольнутую
            $currentGame = $conditionData['player']->currentGames->first();

            if ($currentGame) {
                $currentGame->update(['status' => PlayerGame::REROLLED]);
            }

            // Создаем новую текущую игру
            $fields = [
                'bg_player_id' => $conditionData['player']->id,
                'user_id' => $conditionData['user']->id,
                'status' => PlayerGame::CURRENT,
                'board_game_game_list_id' => $randomGame->id,
                'board_game_id' => $conditionData['boardGame']->id,
                'created_by' => $conditionData['user']->id,
            ];

            if (!PlayerGame::create($fields)) {
                return ErrorService::message(__('boardGame.player_game.create_current_game_error'));
            }

            $this->listTypeHandler($conditionData, $gameListFiltered);
            $this->setTimer($conditionData, $randomGame);

            LogService::addLog(
                $conditionData['user']->id,
                $conditionData['boardGame']->id,
                __('boardGame.player_game.roll_game_and_now_play', ['name' => $randomGame->game->name]),
                $conditionData['player']->id
            );

            return GameListResource::make($randomGame);
        });
    }

    /**
     * Фунция получает список платформ, по которым фильтруются списки игр участника
     *
     * @param array $conditionData
     * @param bool $removeSe
     * @return array
     */
    public function getPlatformIds(array $conditionData, bool $removeSe = false)
    {
        // Проверяем статус эффекты и при необходимости устанавливаем платформу фильтрации
        $playerStatusEffects = $conditionData['player']->statusEffects;

        $platformSlugs = [];

        foreach ($playerStatusEffects as $statusEffect) {
            if ((int)$statusEffect->statusEffectBind->statusEffect->type === StatusEffect::GAME_LIST_TYPE) {
                foreach ($statusEffect->statusEffectBind->statusEffect->actions as $action) {
                    $action = (object)$action;

                    if (isset($action->type) && $action->type === 'platform' && $action->value) {
                        $platformSlugs[] = $action->value;

                        if ($removeSe && $statusEffect->active === true) {
                            $statusEffect->update(['active' => false]);
                        }
                    }
                }
            }

            if ($platformSlugs) break;
        }

        return $platformSlugs;
    }

    /**
     * Проверка, может ли игрок крутить рулетку
     *
     * @param array $conditionData
     * @return array|void
     */
    public function canPlayerRollGame(array $conditionData)
    {
        // Проверяем не выполнил ли игрок условия окончания ивента
        $eventType = $conditionData['boardGame']->settings->where('code', '=', 'event_type')->first();

        if ($eventType && $eventType->value === 'board-last-cell') {
            // Проверяем не достиг ли игрок последней клетки игрового поля
            if ($conditionData['player']->finishBoard) {
                return [
                    'status' => 'error',
                    'status_message' => __('boardGame.player_game.cant_roll_new_game_because_finish_board'),
                ];
            }
        } else {
            // Если настройка event_type не задана, значит используется дефолтный тип окончания ивента - закрытие таймера
            // Проверяем, не превысил ли игрок таймер
            $status = TimerService::getTimerStatus($conditionData['player']->mainTimers->first());

            if ($status && ($status['reached_the_limit'] ?? null)) {
                return [
                    'status' => 'error',
                    'status_message' => __('boardGame.player_game.cant_roll_new_game_because_finish_timer'),
                ];
            }
        }

        // Проверяем нет ли в ивенте ограничения, по количеству отрицательных очков
        $maxNegativePoints = $conditionData['boardGame']->settings->where('code', '=', 'max_negative_points_for_roll_game')->first();

        if ($maxNegativePoints && (int) $maxNegativePoints->value > $conditionData['player']->points) {
            return [
                'status' => 'error',
                'status_message' => __('boardGame.player_game.cant_roll_new_game_because_have_so_many_negative_points', [
                    'negativePoints' => (int) $maxNegativePoints->value,
                    'playerPoints' => $conditionData['player']->points,
                ]),
            ];
        }

        // Проверяем использовал ли игрок доступные крутки предметов и доступные ходы
        if ((!$conditionData['player']->finishBoard && $conditionData['player']->step_count > 0)
            || $conditionData['player']->item_roll_count > 0) {
            return [
                'status' => 'error',
                'status_message' => __('boardGame.player_game.you_must_use_item_rolls_and_board_steps'),
            ];
        }
    }

    /**
     * Фунция сбрасывает счетчики в зависимости от типа списка
     *
     * @param array $conditionData
     * @param array $gameListFiltered
     */
    public function listTypeHandler(array $conditionData, array $gameListFiltered): void
    {
        // Если игрок крутанул список рерольутых игр, то сбрасывает счетчик собственных рерольнутых игр
        $rerolledOwnGameCountForRerolledList = $conditionData['boardGame']
            ->settings
            ->firstWhere('code', 'rerolled_own_game_count_for_rerolled_list')
            ?->value ?? 2;

        if (
            $gameListFiltered['listType'] === 'rerolled'
            && $conditionData['player']->rerolled_own_game_count >= $rerolledOwnGameCountForRerolledList
        ) {
            $conditionData['player']->rerolled_own_game_count = 0;
            $conditionData['player']->save();
        }

        // Если игрок крутанул список золотых игр, то сбрасывает текущее количество собственных рерольнутых игр
        $rerolledGameCountForGoldList = $conditionData['boardGame']
            ->settings
            ->firstWhere('code', 'rerolled_game_count_for_gold_list')
            ?->value ?? 3;

        if ($gameListFiltered['listType'] === 'golden' && $conditionData['player']->rerolled_game_count >= $rerolledGameCountForGoldList) {
            $conditionData['player']->rerolled_game_count = 0;
            $conditionData['player']->save();
        }
    }

    public function setTimer(array $conditionData, $randomGame): void
    {
        $eventType = $conditionData['boardGame']->settings->where('code', '=', 'event_type')->first();

        /**
         * Если тип ивента board-last-cell (достижение последней клетки ивента), то сбрасываем основной таймер
         * и меняем его название
         */
        if ($eventType && $eventType->value === 'board-last-cell') {
            $timer = $conditionData['player']->mainTimers->first();
            $timer->name = $randomGame->game->name;
            $timer->save();

            TimerService::reset($conditionData['boardGame'], $conditionData['player'], $timer);
        }
    }

    /**
     * Функция делает выборку игр, доступных для крутки игроку в текущей ситуации
     *
     * @param $conditionData array Массив данных проверки игрока и содержащих объекты игрока и настольной игры
     * @return array Массив игр
     */
    public function getFilteredGameList(
        array $conditionData,
        bool $removeSe = false,
        bool $setGameList = true
    )
    {
        $listType = 'default'; // Тип списка игр

        $conditionData['boardGame']->load(['settings']);

        $boardGame = $conditionData['boardGame'];
        $player = $conditionData['player'];
        $user = $conditionData['user'];
        $boardGameId = $boardGame->id;

        $gameListQuery = BoardGameGameList::query()
            ->where('board_game_id', $boardGameId);

        if ($setGameList) {
            // Проверяем, существует ли тип списка, который устанавливается из статус эффекта
            $listTypeFromSe = $this->getListTypeFromSe($conditionData, $removeSe);

            if ($listTypeFromSe) {
                $listType = $listTypeFromSe;
            }

            // Рулетка рерольнутых игр (извлекает все уникальные рерольнутые игры, всех игроков)
            $rerolledOwnGameCountForRerolledList = $boardGame
                ->settings
                ->firstWhere('code',  'rerolled_own_game_count_for_rerolled_list')
                ?->value ?? 2;

            if ((int)$player->rerolled_own_game_count >= (int)$rerolledOwnGameCountForRerolledList || $listType === 'rerolled') {
                $rerolledIds = $this->rerolledGamesIds($boardGameId);

                if (!empty($rerolledIds)) {
                    $gameListQuery->whereIn('id', $rerolledIds);
                }

                $gameListQuery->whereNull('list_type');
                $listType = 'rerolled';
            }

            // Рулетка "Золотая коллекция" (только игры, отпечанные как gold)
            $rerolledGameCountForGoldList = $boardGame
                ->settings
                ->firstWhere('code', 'rerolled_game_count_for_gold_list')
                ?->value ?? 3;

            if (($listType !== 'rerolled' && $player->rerolled_game_count >= $rerolledGameCountForGoldList) || $listType === 'golden') {
                $gameListQuery->where('list_type', BoardGameGameList::GOLDEN_LIST);

                $listType = 'golden';
            }

            if ($listType === 'myOwnGame') {
                $gameListQuery->where('added_by', $user->id);
            }
        }

        if ($listType === 'default') {
            $gameListQuery->where('list_type', null);
        }

        $platformSlugs = $this->getPlatformIds($conditionData, $removeSe);

        if ($platformSlugs) {
            $platformIds = GamingPlatform::query()
                ->whereIn('slug', $platformSlugs)
                ->pluck('id');
        }

        if (isset($platformIds)) {
            // Фильтрация по платформе если она есть
            $platformIds = (array) $platformIds;
            $gameListQuery->whereIn('gaming_platform_id', $platformIds);
        } else if (
                $listType !== 'rerolled'
                && $listType !== 'myOwnGame'
                && (bool) $boardGame->settings->firstWhere('code', 'hasExceptionPlatforms')?->value('value')
                && $player->settings
                && isset($player->settings['exceptionPlatforms'])
                && $player->settings['exceptionPlatforms']
            ) {
            // Если платформы нет, то исключаем выбранные (на исключение) пользователем платформы
            $gameListQuery->whereNotIn('gaming_platform_id', $player->settings['exceptionPlatforms']);
        }

        $gameList = $gameListQuery
            ->with([
                'game',
                'game.titleImage',
            ])
            ->active()
            ->get();

        // Убираем из списка игры, которые выпадали игроку
        $playerGameListQuery = PlayerGame::query()
            ->where('board_game_id', $boardGameId)
            ->where('user_id', $user->id);

        // Если это список рерольнутых, то наоборот добавляем рерольнутые игроком игры
        if ($listType === 'rerolled') {
            $playerGameListQuery->where('status', '!=', PlayerGame::REROLLED);
        }

        $playerGameList = $playerGameListQuery->get();

        $usedGames = [];

        foreach ($playerGameList as $game) {
            $usedGames[] = $game->board_game_game_list_id;
        }

        $finalGameList = $gameList->filter(function ($value) use ($usedGames) {
            return !in_array($value->id, $usedGames);
        });

        /**
         * Если золотой список или список реролов игр пуст,
         * то обноляем количество рерольнутых игр игрока и формируем новый список игр
         */
        if (($listType === 'golden' || $listType === 'rerolled') && count($finalGameList) === 0) {
            $player->rerolled_game_count = 0;
            $player->save();

            return $this->getFilteredGameList($conditionData, $removeSe, false);
        }

        return [
            'gameList' => $finalGameList,
            'listType' => $listType,
        ];
    }

    /**
     * Функция проверяет существует ли у игрока тип списка, который устанавливается из статус эффекта.
     * Если статус эффектов несколько, то они накладываются один за другим
     *
     * @param array $conditionData
     * @param bool $removeSe
     * @return |null
     */
    public function getListTypeFromSe(array $conditionData, bool $removeSe = false)
    {
        // Проверяем статус эффекты и при необходимости устанавливаем платформу фильтрации
        $playerStatusEffects = $conditionData['player']->statusEffects->where('active', true);

        $listType = null;

        foreach ($playerStatusEffects as $statusEffect) {
            if ((int)$statusEffect->statusEffectBind->statusEffect->type === StatusEffect::GAME_LIST_TYPE) {
                foreach ($statusEffect->statusEffectBind->statusEffect->actions as $action) {
                    $action = (object)$action;

                    if (isset($action->type) && $action->type === 'listType' && $action->value) {
                        $listType = $action->value;

                        if ($removeSe && $statusEffect->active === true) {
                            $statusEffect->update(['active' => false]);
                        }
                    }
                }
            }

            if ($listType) break;
        }

        return $listType;
    }

    /**
     * Возвращает массив с ID рерольнутых игры
     *
     * @param $boardGameId int ID настольной игры
     * @return array Массив с ID рерольнутых игр
     */
    public function rerolledGamesIds(int $boardGameId)
    {
        return PlayerGame::query()
            ->findByBoardGame($boardGameId)
            ->where('status', PlayerGame::REROLLED)
            ->distinct()
            ->pluck('board_game_game_list_id')
            ->toArray();
    }
}
