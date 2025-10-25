<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\BoardGamePlayerWithCurrentGameResource;
use App\Http\Resources\BoardGame\GameListResource;
use App\Http\Resources\BoardGame\PlayerInteractionResource;
use App\Models\BoardGame\BoardGameGameList;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\PlayerGame;
use App\Models\BoardGame\PlayerInteractions;
use App\Services\BoardGame\ActionsService;
use App\Services\BoardGame\GameService;
use App\Services\BoardGame\InteractionsService;
use App\Services\BoardGame\LogService;
use App\Services\BoardGame\PlayerGameService;
use App\Services\BoardGame\TimerService;
use App\Services\CommentService;
use App\Services\ErrorService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class PlayerGameController extends Controller
{
    public function getPlayerList($slug, Request $request)
    {
        $conditionData = PlayerGameService::checkConditions($slug);

        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        } else {
            $games = $this->getFilteredGameList($request->platform_id, $conditionData);

            $coopInteractions = PlayerInteractions::findByBoardGame($conditionData['boardGame']->id)->active()
                ->where('type', 'inviteToCoop')
                ->whereIn('status', [PlayerInteractions::STATUS_ACTIVE, PlayerInteractions::STATUS_ACCEPTED])
                ->where('created_by', $conditionData['user']->id)->get();

            return [
                'status' => 1,
                'coopInteraction' => PlayerInteractionResource::collection($coopInteractions),
                'games' => isset($games['gameList']) ? GameListResource::collection($games['gameList']) : null,
                'listType' => isset($games['listType']) ? $games['listType'] : null,
                'player' => BoardGamePlayerWithCurrentGameResource::make($conditionData['player']),
            ];
        }
    }

    public function add(Request $request)
    {
        $conditionData = PlayerGameService::checkConditions($request->slug);

        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        } else {
            $fields = array_merge(
                [
                    'user_id' => $conditionData['user']->id,
                    'board_game_game_list_id' => $request->board_game_game_list_id,
                    'board_game_id' => $conditionData['boardGame']->id,
                    'created_by' => $conditionData['user']->id,
                ],
                $this->getFields($request),
            );

            $game = PlayerGame::create($fields);

            if ($game) {
                $message = 'отметил игру ' . $game->game->name . ' как ';

                if ($request->type === PlayerGame::CURRENT) {
                    $message .= 'текущую';
                } else if ($request->type === PlayerGame::REROLLED) {
                    $message .= 'рерольнутую';
                } else if ($request->type === PlayerGame::COMPLETED) {
                    $message .= 'пройденную';
                } else if ($request->type === PlayerGame::GIVEN_AWAY) {
                    $message .= 'отданную';
                }

                if ($message) {
                    if ($request->comment) {
                        $message .= ' и оставил мнение об игре ' . $request->comment;
                    }

                    LogService::addLog($conditionData['user']->id, $conditionData['boardGame']->id, $message);
                }
            }

            return $game;
        }
    }

    public function update(Request $request, PlayerGame $playerGame)
    {
        $conditionData = PlayerGameService::checkConditions($request->slug);

        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        } else {
            $playerCurrentGame = $playerGame::where('board_game_id', $conditionData['boardGame']->id)
                ->where('user_id', $conditionData['user']->id)
                ->where('status', PlayerGame::CURRENT)->first();

            if ($playerCurrentGame) {
                $fields = $this->getFields($request);

                if ($result = $playerCurrentGame->update($fields)) {
                    /* Рерол игры */
                    if ($request->type === PlayerGame::REROLLED) {
                        /* Добавление предмета при рероле */
//                    $fields = $request->validate([
//                        'board_game_id' => 'required',
//                    ]);
//
//                    $boardGameItems = ItemBind::query()->where('slug', 'tuhlyi-banan')->first();
//
//                    $fields['board_game_item_id'] = $boardGameItems->id;
//                    $fields['user_id'] = $user->id;
//                    $fields['created_by'] = $user->id;
//
//                    BoardGameInventory::create($fields);

                        /* Отнимает очки при рероле и сбрасываем стрик при рероле */
                        $subtractPointsSetting = $conditionData['boardGame']->settings->where('code', '=', 'subtract_points')->first();
                        $subtractPointsCount = $subtractPointsSetting ? $subtractPointsSetting->value : 25;

                        $conditionData['player']->points = $conditionData['player']->points - $subtractPointsCount;
                        $conditionData['player']->streak = 0;

                        /* Если игрок рерольнул свою игру, то обновляем счетчик своих рерольнутых игр */
                        if ($playerCurrentGame->game->added_by === $conditionData['player']->user_id) {
                            $conditionData['player']->rerolled_own_game_count = $conditionData['player']->rerolled_own_game_count + 1;
                        }

                        $conditionData['player']->save();

                        // Игра из очереди
                        $this->gameFromQueue($conditionData);

                        $message = 'рерольнул игру ' .  $playerCurrentGame->game->game->name . ' и потерял ' . $subtractPointsCount . ' очков';

                        /* Проверяем взаимодействия */
                        $interactionsService = new InteractionsService();
                        $interactionsService->checkInteractionAfterActionWithGame($request->type, $conditionData);
                    }

                    /* Игра пройдена */
                    if ($request->type === PlayerGame::COMPLETED) {
                        /* Рассчитываем количество очков за игру */
                        $pointsForGame = GameService::calcPoints($playerCurrentGame->game);

                        // Добавляем очки за стрик
                        if ($conditionData['player']->streak > 0) {
                            $finalPoints = $pointsForGame + ($pointsForGame / 100 * ($conditionData['player']->streak * 2));
                        }

                        $conditionData['player']->points = $conditionData['player']->points + $finalPoints;

                        /* Добавляем стрик, если он не достиг максимального */
                        $maxStreakSetting = $conditionData['boardGame']->settings->where('code', '=', 'max_string')->first();
                        $maxStreak = $maxStreakSetting ? $maxStreakSetting->value : 5;

                        if ($conditionData['player']->streak < $maxStreak && $conditionData['player']->streak !== $maxStreak) {
                            $conditionData['player']->streak++;
                        }

                        /* Добавляем ролл предметы и добавляем ходы */
                        $conditionData['player']->item_roll_count = $conditionData['player']->item_roll_count + 1;
                        $conditionData['player']->step_count = $conditionData['player']->step_count + 1;

                        $conditionData['player']->save();

                        // Игра из очереди
                        $this->gameFromQueue($conditionData);

                        $message = 'прошел игру ' .  $playerCurrentGame->game->game->name . ' и получил за неё ' . $pointsForGame . ' очков';

                        if ($request->time) {
                            $formattedTime = sprintf("%02d:%02d:%02d",
                                floor($request->time / 3600),
                                floor(($request->time % 3600) / 60),
                                $request->time % 60
                            );

                            if ($formattedTime) {
                                $message .= ', затратил ' . $formattedTime;
                            }
                        }

                        // Проверяем, есть ли активное и принятое приглашение в кооп от этого игрока и есть есть, то добавляем напарнику по коопу очки
                        $coopInteraction = PlayerInteractions::findByBoardGame($conditionData['boardGame']->id)->active()
                            ->where('type', 'inviteToCoop')
                            ->where('status', PlayerInteractions::STATUS_ACCEPTED)
                            ->where('created_by', $conditionData['user']->id)->first();

                        if ($coopInteraction && $playerCurrentGame->game->coop) {
                            $player = BoardGamePlayer::findByBoardGame($coopInteraction->board_game_id)->findByUserId($coopInteraction->with_player)->active()->first();

                            if ($player) {
                                $player->points = $player->points + $pointsForGame / 2;
                                $player->save();

                                $coopInteraction->active = false;
                                $coopInteraction->save();

                                NotificationService::set(
                                    [
                                        'user_id' => $player->user->id,
                                        'message' => 'За помощь в прохождении игры ' . $playerCurrentGame->game->game->name . ' вы получаете ' . $pointsForGame / 2 . ' очков'
                                    ]
                                );
                            }
                        }

                        /* Проверяем взаимодействия */
                        $interactionsService = new InteractionsService();
                        $interactionsService->checkInteractionAfterActionWithGame($request->type, $conditionData);
                    }

                    /* Игра отдана */
                    if ($request->type === PlayerGame::GIVEN_AWAY) {
                        $message = 'отдал игру ' .  $playerCurrentGame->game->game->name;
                    }

                    if ($message) {
                        if ($request->comment) {
                            $message .= ' и оставил мнение об игре ' . $request->comment;
                        }

                        LogService::addLog($conditionData['user']->id, $conditionData['boardGame']->id, $message);
                    }

                    return $result;
                }
            } else {
                return ErrorService::message('Не найдено текущей игры');
            }
        }
    }

    private function gameFromQueue($conditionData)
    {
        /* Если есть игра в очереди, то делаем её текущей */
        $playerCurrentGameInQueue = PlayerGame::where('board_game_id', $conditionData['boardGame']->id)
            ->where('user_id', $conditionData['user']->id)
            ->where('status', PlayerGame::QUEUE)
            ->orderBy('id', 'desc')
            ->first();

        if ($playerCurrentGameInQueue) {
            $playerCurrentGameInQueue->status = PlayerGame::CURRENT;
            $playerCurrentGameInQueue->save();

            NotificationService::set(
                [
                    'user_id' => $conditionData['user']->id,
                    'message' => 'Игра ' . $playerCurrentGameInQueue->game->game->name . ' установлена как текущая, так как была в очереди'
                ]
            );
            LogService::addLog(
                $conditionData['user']->id,
                $conditionData['boardGame']->id,
                'Теперь проходит игру ' . $playerCurrentGameInQueue->game->game->name
            );
        }
    }

    public function inviteToCoop(Request $request)
    {
        if ($request->slug) {
            $conditionData = PlayerGameService::checkConditions($request->slug);

            if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
                return $conditionData;
            } else {
                $boardGameGameList = BoardGameGameList::findById($request->board_game_game_list_id)->first();
                // [{"name": "ТМNT 4 (NES)", "type": "playerInteractions", "value": "battleForPoints", "target": "other", "description": "Битва за 20 очков в игре ТМNT 4 (NES) с другим участником ивента", "pointsForWin": 20}]

                $action = (Object)[
                    'type' => 'playerInteractions',
                    'target' => 'other',
                    'value' => 'inviteToCoop',
                    'description' => 'Приглашение пройти в коопе игру ' . $boardGameGameList->game->name,
                ];

                $actionService = new ActionsService($conditionData, 'interactions', null);
                return $actionService->activateAction($request, $action);
            }
        } else {
            return ErrorService::message('Не получен slug');
        }
    }

    public function getFields($request)
    {
        $fields = [
            'status' => $request->type,
            'time' => $request->time,
        ];

        if ($request->comment) {
            $newComment = [
                'message' => $request->comment,
                'entity_type' => $request->entity_type,
                'entity_id' => $request->entity_id,
            ];

            $comment = CommentService::addComment($request, $newComment);

            $fields['comment_id'] = $comment->original->id;
        }

        return $fields;
    }

    public function roll($slug, Request $request, PlayerGame $playerGame) {
        $conditionData = PlayerGameService::checkConditions($slug);

        // Проверяем, что игрок может крутить рулетку игр
        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        } else {
            $gameListFiltered = $this->getFilteredGameList(
                $request->platform_id ? $request->platform_id : null,
                $conditionData
            );

            if (isset($gameListFiltered['gameList']) && $gameListFiltered['gameList']->count() > 0) {
                $randomGame = $gameListFiltered['gameList']->random();

                if ($randomGame) {
                    // Если у игрока есть текущая игра, отмечаем её как рерольнутую
                    $currentGame = PlayerGame::query()
                        ->where('user_id', $conditionData['user']->id)
                        ->where('board_game_id', $conditionData['boardGame']->id)
                        ->where('status', PlayerGame::CURRENT)
                        ->first();

                    if ($currentGame) {
                        $currentGame->update(['status' => PlayerGame::REROLLED]);
                    }

                    // Создаем новую текущую игру
                    $fields = [
                        'user_id' => $conditionData['user']->id,
                        'status' => PlayerGame::CURRENT,
                        'board_game_game_list_id' => $randomGame->id,
                        'board_game_id' => $conditionData['boardGame']->id,
                        'created_by' => $conditionData['user']->id,
                    ];

                    if ($playerGame::create($fields)) {
                        // Если игра была из списка рерольнутых, то сбрасывает счетчик собственных рерольнутых игр
                        if ($gameListFiltered['listType'] === 'rerolled' && $conditionData['player']->rerolled_own_game_count >= 3) {
                            $conditionData['player']->rerolled_own_game_count = 0;
                            $conditionData['player']->save();
                        }

                        return GameListResource::make($randomGame);
                    } else {
                        return ErrorService::message('Ошибка создания новой текущей игры');
                    }
                } else {
                    return ErrorService::message('Ошибка выбора игры');
                }
            } else {
                return ErrorService::message('У вас не осталось игр, для рулетки');
            }
        }
    }

    private function getFilteredGameList($platformId, $conditionData)
    {
        $listType = 'default';
        $boardGameGameQuery = BoardGameGameList::query()->where('board_game_id', $conditionData['boardGame']->id);

        if ($platformId) {
            $boardGameGameQuery->where('gaming_platform_id', $platformId);
        }

        // Рулетка рерольнутых игр
        if ($conditionData['player']->rerolled_own_game_count >= 3) {
            $rerolledGames = PlayerGame::query()->findByBoardGame($conditionData['boardGame']->id)
                ->where('status', PlayerGame::REROLLED)
                ->select('board_game_game_list_id')->get()->unique('board_game_game_list_id');

            $rerolledIds = [];

            foreach ($rerolledGames as $game) {
                $rerolledIds[] = $game['board_game_game_list_id'];
            }

            $boardGameGameQuery->whereIn('id', $rerolledIds);
            $boardGameGameQuery->where('list_type', null);
            $listType = 'rerolled';
        }

        if ($listType !== 'rerolled') {
            // Рулетка "Золотая коллекция"
            $currentPlayerGames = PlayerGame::query()
                ->findByBoardGame($conditionData['boardGame']->id)
                ->findByUserId($conditionData['user']->id)->orderBy('id', 'desc')->get();

            $goldenList = false;
            $rerolledGameInaRow = 0;

            foreach ($currentPlayerGames as $game) {
                if ($game->status === PlayerGame::REROLLED) {
                    $rerolledGameInaRow++;

                    if ($rerolledGameInaRow === 3) {
                        $goldenList = true;
                        break;
                    }
                } else {
                    break;
                }
            }

            if ($goldenList) {
                $boardGameGameQuery->where('list_type', BoardGameGameList::GOLDEN_LIST);
                $listType = 'golden';
            }
        }

        if ($listType === 'default') {
            $boardGameGameQuery->where('list_type', null);
        }

        $boardGameGameList = $boardGameGameQuery->get();

        // Убираем из списка игры, выпадали игроку
        $playerGameListQuery = PlayerGame::query()
            ->where('board_game_id', $conditionData['boardGame']->id)
            ->where('user_id', $conditionData['user']->id);

        // Если это список рерольнутых, то добавляем рерольнутые игроком игры
        if ($listType === 'rerolled') {
            $playerGameListQuery->where('status', '!=', PlayerGame::REROLLED);
        }

        $playerGameList = $playerGameListQuery->get();

        $usedGames = [];

        foreach ($playerGameList as $game) {
            $usedGames[] = $game->board_game_game_list_id;
        }

        // TODO возможно стоит что-то придумать когда игр 0
        return [
            'gameList' => $boardGameGameList->filter(function ($value) use ($usedGames) {
                return !in_array($value->id, $usedGames);
            }),
            'listType' => $listType,
        ];
    }

    public function getSpendTime(Request $request)
    {
        $conditionData = PlayerGameService::checkConditions($request->slug);

        // Проверяем, что игрок может крутить рулетку игр
        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        } else {
            $playerGame = PlayerGame::query()
                ->where('user_id', $conditionData['user']->id)
                ->where('board_game_id', $conditionData['boardGame']->id)
                ->where('status', PlayerGame::CURRENT)
                ->first();

            return TimerService::timeInGame($playerGame);
        }
    }
}
