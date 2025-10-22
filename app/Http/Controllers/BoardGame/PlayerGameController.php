<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\BoardGamePlayerWithCurrentGameResource;
use App\Http\Resources\BoardGame\GameListResource;
use App\Models\BoardGame\BoardGameGameList;
use App\Models\BoardGame\PlayerGame;
use App\Services\BoardGame\ActionsService;
use App\Services\BoardGame\GameService;
use App\Services\BoardGame\InteractionsService;
use App\Services\BoardGame\LogService;
use App\Services\BoardGame\PlayerGameService;
use App\Services\BoardGame\TimerService;
use App\Services\CommentService;
use App\Services\ErrorService;
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

            return [
                'status' => 1,
                'games' => GameListResource::collection($games),
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
                        $subtractPointsCount = $subtractPointsSetting ? $subtractPointsSetting : 25;

                        $conditionData['player']->points = $conditionData['player']->points - $subtractPointsCount;
                        $conditionData['player']->streak = 0;

                        $conditionData['player']->update;

                        // TODO если есть игра в очерели ставим её сюда

                        $message = 'рерольну игру ' .  $playerCurrentGame->game->name . ' и потерял ' . $subtractPointsCount . ' очков';

                        /* Проверяем взаимодействия */
                        $interactionsService = new InteractionsService();
                        $interactionsService->checkInteractionAfterActionWithGame($request->type, $conditionData);
                    }

                    /* Игра пройдена */
                    if ($request->type === PlayerGame::COMPLETED) {
                        /* Добавляем стрик, если он не достиг максимального */
                        $maxStreakSetting = $conditionData['boardGame']->settings->where('code', '=', 'max_string')->first();
                        $maxStreak = $maxStreakSetting ? $maxStreakSetting : 5;

                        if ($conditionData['player']->streak < $maxStreak && $conditionData['player']->streak !== $maxStreak) {
                            $conditionData['player']->streak++;
                        }

                        /* Рассчитываем количество очков за игру */
                        $pointsForGame = GameService::calcPoints($playerCurrentGame);

                        // Добавляем стрик
                        if ($conditionData['player']->streak > 0) {
                            $pointsForGame = $pointsForGame + (($pointsForGame / 100) * ($conditionData['player']->streak * 2));
                        }

                        $conditionData['player']->points = $conditionData['player']->points + $pointsForGame;
                        $conditionData['player']->points->update();

                        /* Если есть игра в очереди, то делаем её текущей */
                        $playerCurrentGame = $playerGame::where('board_game_id', $conditionData['boardGame']->id)
                            ->where('user_id', $conditionData['user']->id)
                            ->where('status', PlayerGame::QUEUE)->first();

                        if ($playerCurrentGame) {
                            $playerCurrentGame->status = PlayerGame::CURRENT;
                            $playerCurrentGame->update();
                        }

//                        $boardGamePlayers = BoardGamePlayer::where('user_id', $conditionData['user'])->where('board_game_id', $conditionData['boardGame']->id)->first();
//
//                        $points = $boardGamePlayers->points + $playerCurrentGame->game->points;
//
//                        $boardGamePlayers->update(['points' => $points]);

                        $message = 'прошел игру ' .  $playerCurrentGame->game->name . ' и получил за неё ' . $pointsForGame . ' очков';

                        if ($request->time) {
                            $formattedTime = sprintf("%02d:%02d:%02d",
                                floor($request->time / 3600),
                                floor(($request->time % 3600) / 60),
                                $request->time % 60
                            );
                        }

                        if ($formattedTime) {
                            $message .= ', затратил ' . $formattedTime;
                        }

                        /* Проверяем взаимодействия */
                        $interactionsService = new InteractionsService();
                        $interactionsService->checkInteractionAfterActionWithGame($request->type, $conditionData);
                    }

                    /* Игра отдана */
                    if ($request->type === PlayerGame::GIVEN_AWAY) {
                        $message = 'отдал игру ' .  $playerCurrentGame->game->name;
                    }

                    if ($message) {
                        if ($request->comment) {
                            $message .= ' и оставил мнение об игре ' . $request->comment;
                        }

                        LogService::addLog($conditionData['user']->id, $conditionData['boardGame']->id, $message);
                    }

                    return $result;
                }
            }
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

            if ($gameListFiltered->count() > 0) {
                $randomGame = $gameListFiltered->random();

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
        $boardGameGameQuery = BoardGameGameList::query()->where('board_game_id', $conditionData['boardGame']->id);

        if ($platformId) {
            $boardGameGameQuery->where('gaming_platform_id', $platformId);
        }

        $boardGameGameList = $boardGameGameQuery->get();

        $playerGameList = PlayerGame::query()
            ->where('board_game_id', $conditionData['boardGame']->id)
            ->where('user_id', $conditionData['user']->id)
            ->get();

        $usedGames = [];

        foreach ($playerGameList as $game) {
            $usedGames[] = $game->board_game_game_list_id;
        }

        return $boardGameGameList->filter(function ($value) use ($usedGames) {
            return !in_array($value->id, $usedGames);
        });
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
