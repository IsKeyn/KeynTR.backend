<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\PlayerGame;

class StatsService
{
    public function getGameList($playerGames)
    {
        $gameList = [];

        foreach ($playerGames as $playerGame) {
            if (isset($gameList[$playerGame->board_game_game_list_id])) {
                if (isset($gameList[$playerGame->board_game_game_list_id]['statuses'][$playerGame->status])) {
                    $gameList[$playerGame->board_game_game_list_id]['statuses'][$playerGame->status]++;
                } else {
                    $gameList[$playerGame->board_game_game_list_id]['statuses'][$playerGame->status] = 1;
                }
            } else {
                $gameList[$playerGame->board_game_game_list_id]  = [
                    'statuses' => [
                        $playerGame->status => 1,
                    ]
                ];
            }
        }

        return $gameList;
    }

    public function getGamesListByStatus($playerGames, $gameList, $status, $limit = 5)
    {
        uasort($gameList, function($a, $b) use ($status) {
            $aValue = $a['statuses'][$status] ?? 0;
            $bValue = $b['statuses'][$status] ?? 0;

            if ($aValue == $bValue) {
                return 0;
            }
            return ($aValue > $bValue) ? -1 : 1;
        });

        $gameCollection = collect();

        $i = 0;
        foreach ($gameList as $gameId => $data) {
            if (isset($data['statuses'][$status])) {
                $game = $playerGames->where('board_game_game_list_id', $gameId)->first();
                $game->setAttribute('additional_data', $data['statuses'][$status]);
                $gameCollection->push($game);

                $i++;

                if ($i === $limit) {
                    break;
                }
            }
        }

        return $gameCollection->sortByDesc('additional_data');
    }

    public function getUserWhoMostUseItem($itemId, $boardGameId, $limit = 5)
    {
        $playerMostUseItem = BoardGameInventory::query()
            ->where('board_game_item_id', '=', $itemId)
            ->where('has_used', '=', true)
            ->where('board_game_id', $boardGameId)
            ->get();

        $itemUseCount = [];

        foreach ($playerMostUseItem as $playerWithItem) {
            if (isset($itemUseCount[$playerWithItem->user_id])) {
                $itemUseCount[$playerWithItem->user_id]++;
            } else {
                $itemUseCount[$playerWithItem->user_id] = 1;
            }
        }

        $boardGamePlayers = BoardGamePlayer::whereIn('user_id', array_keys($itemUseCount))->get();

        $result = collect([]);

        $i = 0;
        foreach ($boardGamePlayers as $player) {
            if (isset($itemUseCount[$player->user_id])) {
                $player->setAttribute('additional_data', $itemUseCount[$player->user_id]);
                $result->push($player);

                $i++;

                if ($i === $limit) {
                    break;
                }
            }
        }

        return $result->sortByDesc('additional_data');
    }

    public function getGamesByTime($boardGameId, $sort = 'asc', $limit = 5)
    {
        $shortestGames = PlayerGame::query()
            ->where('time', '!=', null)
            ->where('board_game_id', $boardGameId)
            ->orderBy('time', $sort)
            ->get();

        $result = collect([]);

        $tempResult = array();

        foreach ($shortestGames as $game) {
            if (!isset($tempResult[$game->board_game_game_list_id])) {
                $tempResult[$game->board_game_game_list_id] = [
                    'id' => $game->id,
                    'time' => $game->time,
                ];
            } else {
                if ($sort === 'asc' && $tempResult[$game->board_game_game_list_id]['time'] > $game->time) {
                    $tempResult[$game->board_game_game_list_id] = [
                        'id' => $game->id,
                        'time' => $game->time,
                    ];
                }

                if ($sort === 'desc' && $tempResult[$game->board_game_game_list_id]['time'] < $game->time) {
                    $tempResult[$game->board_game_game_list_id] = [
                        'id' => $game->id,
                        'time' => $game->time,
                    ];
                }
            }
        }

        $i = 0;
        foreach ($shortestGames as $game) {
            /* Использован foreach так как массив элементов может быть достаточно большой */
            $exists = false;
            foreach ($tempResult as $item) {
                if ($item['id'] == $game->id) {
                    $exists = true;
                    break;
                }
            }

            if ($exists) {
                $result->push($game);

                $i++;

                if ($i === $limit) {
                    break;
                }
            }
        }

        return $result;
    }
}
