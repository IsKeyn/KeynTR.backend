<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\BoardGameGameList;
use App\Models\BoardGame\PlayerGame;
use App\Services\Entity\EntityService;

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
     * Функция делает выборку игр, доступных для крутки игроку в текущей ситуации
     *
     * @param $platformIds integer|array ID платформы или массив ID платформ для выборки
     * @param $conditionData array Массив данных проверки игрока и содержащих объекты игрока и настольной игры
     * @return array Массив игр
     */
    public function getFilteredGameList(
        int|array|null $platformIds,
        array $conditionData
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

        // Рулетка рерольнутых игр (извлекает все уникальные рерольнутые игры, всех игроков)
        $rerolledOwnGameCountForRerolledList = $boardGame
            ->settings
            ->firstWhere('code', 'rerolled_own_game_count_for_rerolled_list')
            ?->value('value') ?? 2;

        if ($player->rerolled_own_game_count >= $rerolledOwnGameCountForRerolledList) {
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
                ?->value('value') ?? 3;

        if ($listType !== 'rerolled' && $player->rerolled_game_count >= $rerolledGameCountForGoldList) {
            $gameListQuery->where('list_type', BoardGameGameList::GOLDEN_LIST);

            $listType = 'golden';
        }

        if ($listType === 'default') {
            $gameListQuery->where('list_type', null);
        }

        if ($platformIds) {
            // Фильтрация по платформе если она есть
            $platformIds = (array) $platformIds;
            $gameListQuery->whereIn('gaming_platform_id', $platformIds);
        } else if (
                $listType !== 'rerolled'
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

        // Если золотой список игр пуст, то обноляем количество рерольнутых игр игрока и формируем новый список игр
        if ($listType === 'golden' && count($finalGameList) === 0) {
            $player->rerolled_game_count = 0;
            $player->save();

            return $this->getFilteredGameList($platformIds, $conditionData);
        }

        return [
            'gameList' => $finalGameList,
            'listType' => $listType,
        ];
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
            ->pluck('board_game_game_list_id')
            ->distinct()
            ->toArray();
    }
}
