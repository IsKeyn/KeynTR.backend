<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\BoardGamePlayerWithCurrentGameResource;
use App\Http\Resources\BoardGame\GameListResource;
use App\Models\BoardGame\BoardGameGameList;
use App\Models\BoardGame\PlayerGame;
use App\Services\BoardGame\InteractionsService;
use App\Services\BoardGame\PlayerGameService;
use App\Services\BoardGame\TimerService;
use App\Services\CommentService;
use Illuminate\Http\Request;

class PlayerGameController extends Controller
{
    public function getPlayerList($slug, Request $request)
    {
        $conditionData = PlayerGameService::checkConditions($slug);

        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        } else {
            $games = $this->getFilteredGameList($request, $conditionData);

            return [
                'status' => 1,
                'games' => GameListResource::collection($games),
                'player' => BoardGamePlayerWithCurrentGameResource::make($conditionData['player']),
            ];
        }
    }

    public function add(Request $request)
    {
        $user = $request->user();

        $fields = array_merge(
            [
                'user_id' => $user->id,
                'board_game_game_list_id' => $request->board_game_game_list_id,
                'board_game_id' => $request->board_game_id,
                'created_by' => $user->id,
            ],
            $this->getFields($request),
        );

        return PlayerGame::create($fields);
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
                    if ($request->type === 1) {
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
                        $subtractPointsSetting = $conditionData['boardGame']->settings->where('code', '=', '$subtract_points')->first();
                        $subtractPointsCount = $subtractPointsSetting ? $subtractPointsSetting : 25;

                        $conditionData['player']->points = $conditionData['player']->points - $subtractPointsCount;
                        $conditionData['player']->streak = 0;

                        $conditionData['player']->update;

                        // TODO если есть игра в очерели ставим её сюда
                        // TODO добавляем логи

                        /* Проверяем взаимодействия */
                        $interactionsService = new InteractionsService();
                        $interactionsService->checkInteractionAfterActionWithGame($request->type, $conditionData);
                    }

                    /* Игра пройдена */
                    if ($request->type === 2) {
                        /* Добавляем стрик, если он не достиг максимального */
                        $maxStreakSetting = $conditionData['boardGame']->settings->where('code', '=', 'max_string')->first();
                        $maxStreak = $maxStreakSetting ? $maxStreakSetting : 5;

                        if ($conditionData['player']->streak < $maxStreak && $conditionData['player']->streak !== $maxStreak) {
                            $conditionData['player']->streak++;
                        }

                        /* Рассчитываем количество очков за игру */
                        $pointsForGame = round($playerCurrentGame->game->difficult * ($playerCurrentGame->game->game_completion_time / 60));
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

                        // TODO добавляем логи

                        /* Проверяем взаимодействия */
                        $interactionsService = new InteractionsService();
                        $interactionsService->checkInteractionAfterActionWithGame($request->type, $conditionData);
                    }

                    return $result;
                }
            }
        }
    }

    public function getFields($request)
    {
        $fields = [];

        switch ($request->type) {
            case 0: $fields['status'] = PlayerGame::CURRENT; break;
            case 1: $fields['status'] = PlayerGame::REROLLED; break;
            case 2: $fields['status'] = PlayerGame::COMPLETED; break;
            case 3: $fields['status'] = PlayerGame::GIVEN_AWAY; break;
        }

        if ($request->hourCount) {
            $fields['time'] = $request->hourCount;
        }

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

    public function roll(Request $request, PlayerGame $playerGame) {
        $gameListFiltered = $this->getFilteredGameList($request);

        if ($gameListFiltered->count() > 0) {
            $randomGame = $gameListFiltered->random();

        if ($randomGame) {
            $user = $request->user();

            $currentGame = PlayerGame::query()
                ->where('user_id', $user->id)
                ->where('board_game_id', $request->board_game_id)
                ->where('status', PlayerGame::CURRENT)
                ->first();

            if ($currentGame) {
                $currentGame->update(['status' => PlayerGame::REROLLED]);
            }

            $status = PlayerGame::CURRENT;

            $fields = [
                'user_id' => $user->id,
                'status' => $status,
                'board_game_game_list_id' => $randomGame->id,
                'board_game_id' => $request->board_game_id,
                'created_by' => $user->id,
            ];

            $playerGame::create($fields);

            return GameListResource::make($randomGame);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function getFilteredGameList($request, $conditionData)
    {
        $boardGameGameQuery = BoardGameGameList::query()->where('board_game_id', $conditionData['boardGame']->id);

        if ($request->platform_id) {
            $boardGameGameQuery->where('gaming_platform_id', $request->platform_id);
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
        $user = $request->user();

        if ($user) {
            $playerGame = PlayerGame::query()
                ->where('user_id', $user->id)
                ->where('board_game_id', $request->board_game_id)
                ->where('status', PlayerGame::CURRENT)
                ->first();

            return TimerService::timeInGame($playerGame);
        }
    }
}
