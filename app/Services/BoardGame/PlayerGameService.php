<?php

namespace App\Services\BoardGame;

use App\Http\Resources\BoardGame\GameListResource;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGameGameList;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\BoardGamePlayerPosition;
use App\Models\BoardGame\PlayerGame;
use App\Services\ErrorService;
use Illuminate\Support\Facades\Auth;

class PlayerGameService
{
    // TODO Грязный сервайс, разнести методы по BgPlayerService и BgPlayerGameService и удалить
    public static function joinTheGame($user, $slug)
    {
        if ($user && $slug) {
            $boardGame = BoardGame::findBySlug($slug)->first();

            $player = BoardGamePlayer::findByBoardGame($boardGame->id)->findByUserId($user->id)->first();

            if ($player) {
                return ErrorService::message('Данный пользователь уже участвует в ивенте');
            }

            $itemRollCountSetting = $boardGame->settings->where('code', '=', 'item_roll_default_count')->first();
            $stepCountSetting = $boardGame->settings->where('code', '=', 'step_default_count')->first();
            $typeSettings = $boardGame->settings->where('code', '=', 'type')->first();

            if ($boardGame) {
                if ($typeSettings === 'registrationIsClose') {
                    return [
                        'status' => 'error',
                        'status_message' => 'Регистрация на ивент закрыта',
                    ];
                }

                // Ставим игрока на игровое поле
                $positionFields = [
                    'user_id' => $user->id,
                    'position' => 1,
                    'board_game_id' => $boardGame->id,
                    'created_by' => $user->id,
                ];
                BoardGamePlayerPosition::create($positionFields);

                // Устанавливаем таймер для игрока
                TimerService::createTimer($boardGame->id, $user, 'main');

                $fields = [
                    'user_id' => $user->id,
                    'board_game_id' => $boardGame->id,
                    'item_roll_count' => $itemRollCountSetting ? $itemRollCountSetting->value : 2,
                    'step_count' => $stepCountSetting ? $stepCountSetting->value : 1,
                    'active' => $typeSettings ? ($typeSettings->value === 'upon-request' ? false : true) : true,
                    'not_active_reason' => $typeSettings ? ($typeSettings->value === 'upon-request' ? 'Ожидает одобрения модератора' : null) : null,
                    'created_by' => $user->id,
                ];

                if (BoardGamePlayer::create($fields)) {
                    switch ($typeSettings->value ?? null) {
                        case 'upon-request':
                            $returnMessage = 'Заявка на участие успешно отправлена и рассматривается модераторами';
                            $logMessage = 'подал заявку на участие в ивенте';
                            break;
                        default:
                            $returnMessage = 'Вы успешно зарегистрированы в ивенте';
                            $logMessage = 'присоединился к ивенту';
                            break;
                    }

                    LogService::addLog(
                        $user->id,
                        $boardGame->id,
                        $logMessage
                    );

                    return [
                        'status' => 'success',
                        'status_message' => $returnMessage,
                    ];
                }
            } else {
                return [
                    'status' => 'error',
                    'status_message' => 'Ивент не найден',
                ];
            }
        }
    }

    public static function actionsWithGame($gameListGameId, $boardGameId)
    {
        $playerGame = PlayerGame::query()
            ->where('board_game_game_list_id', $gameListGameId)
            ->where('board_game_id', $boardGameId)
            ->where('status', '!=',PlayerGame::CURRENT)
            ->get();

        return $playerGame;
    }

    public static function actionsWithGameInOtherEvents($game, $boardGameId)
    {
        $boardGames = BoardGame::query()
            ->whereNotIn('slug', ['demo'])
            ->where('id', '!=', $boardGameId)
            ->get();

        $gameListIds = [];

        foreach ($boardGames as $boardGame) {
            $gameListId = BoardGameGameList::query()->where('game_id', $game->game_id)->where('board_game_id', $boardGame->id)->value('id');

            if ($gameListId) {
                $gameListIds[] = $gameListId;
            }
        }

        $playerGame = PlayerGame::query()
            ->whereIn('board_game_game_list_id', $gameListIds)
            ->where('status', '!=', PlayerGame::CURRENT)
            ->get();

        return $playerGame;
    }

    public static function checkConditions($slug)
    {
        $user = Auth::user();

        if (!$user) {
            return [
                'status' => 'error',
                'status_message' => 'Функционал доступен только авторизованному пользователю',
            ];
        }

        $boardGame = BoardGame::findBySlug($slug)->active()->first();

        if (!$boardGame) {
            return [
                'status' => 'error',
                'status_message' => 'Ивент не найден или не активен',
            ];
        }

        if ($boardGame->status === 0 || $boardGame->status === 2) {
            return [
                'status' => 'error',
                'status_message' => $boardGame->status === 0 ? 'Ивент закончился' : 'Ивент скоро начнется',
            ];
        }

        $player = BoardGamePlayer::where('user_id', $user->id)
            ->where('board_game_id', $boardGame->id)
            ->with('mainTimers')
            ->first();

        if (!$player) {
            return [
                'status' => 'error',
                'status_message' => 'Вы должны участвовать в этом ивенте, чтобы иметь доступ к данному функционалу',
            ];
        }

        if (!$player->active) {
            return [
                'status' => 'error',
                'status_message' => 'Вы должны быть активным участником ивента, причина не активности: ' . $player->not_active_reason,
            ];
        }

        return [
            'boardGame' => $boardGame,
            'user' => $user,
            'player' => $player,
        ];
    }

    public static function getAvailablePlayerGameList($boardGameId, $userId)
    {
        $boardGameGameQuery = BoardGameGameList::findByBoardGame($boardGameId);
        $boardGameGameQuery->where('list_type', null);
        $boardGameGameList = $boardGameGameQuery->active()->get();

        // Убираем из списка игры, выпадали игроку
        $playerGameListQuery = PlayerGame::query()
            ->findByBoardGame($boardGameId)
            ->findByUserId($userId);

        $playerGameList = $playerGameListQuery->get();

        $usedGames = [];

        foreach ($playerGameList as $game) {
            $usedGames[] = $game->board_game_game_list_id;
        }

        return GameListResource::collection($boardGameGameList->filter(function ($value) use ($usedGames) { return !in_array($value->id, $usedGames); }));
    }
}
