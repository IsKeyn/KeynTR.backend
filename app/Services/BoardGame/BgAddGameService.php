<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\AddGame;
use App\Models\BoardGame\PlayerGame;
use App\Services\Entity\EntityService;
use Symfony\Component\HttpFoundation\Response;

class BgAddGameService
{
    public static function getById(
        $id,
        $forceRefresh = false,
        $withTrashed = false
    )
    {
        return EntityService::getById(
            AddGame::class,
            AddGame::CACHE_SERVICE,
            AddGame::DETAIL_RESOURCE,
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

    public static function checkCanPlayerAddGame($conditionData)
    {
        if ($conditionData['player']->added_games) {
            return [
                'status' => AddGame::STATUS_ALREADY_ADDED,
                'message' => __('boardGame.add_game.already_added'),
            ];
        }

        // Проверям условия, может ли игрок добавлять игры
        $conditionData['boardGame']->load([
            'settings'
        ]);

        $conditionData['player']->load([
            'positions' => function ($query) {
                $query->active()->orderBy('id', 'desc');
            },
            'games' => function ($query) {
                $query->where('status', PlayerGame::COMPLETED);
            },
        ]);

        $position = $conditionData['player']->positions->sortByDesc('id')->first()->position;
        $finishedGames = $conditionData['player']->games->where('status', PlayerGame::COMPLETED)->count();

        $addingGamesConditions = $conditionData['boardGame']
            ->settings
            ->where('code', 'addingGamesConditions')
            ->value('value');

        if ($addingGamesConditions) {
            $addingGamesConditions = json_decode($addingGamesConditions, true);
        }

        if ($conditionData['player']->premium ||
            ($position >= (isset($addingGamesConditions['position']) ? $addingGamesConditions['position'] : 0)
            && $finishedGames >= (isset($addingGamesConditions['finishedGames']) ? $addingGamesConditions['finishedGames'] : 0))
        ) {
            $status = AddGame::STATUS_CAN_ADD;
            $message = __('boardGame.add_game.can_add');
        } else {
            $status = AddGame::STATUS_CANT_ADD;
            $message = __('boardGame.add_game.cant_add');
        }

        return [
            'status' => $status,
            'message' => $message,
            'data' => [
                'position' => $position,
                'finishedGames' => $finishedGames,
            ],
        ];
    }
}
