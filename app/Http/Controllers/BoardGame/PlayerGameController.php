<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\GameListResource;
use App\Models\BoardGame\BoardGameGameList;
use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\BoardGameItem;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\PlayerGame;
use App\Services\BoardGame\TimerService;
use App\Services\CommentService;
use Illuminate\Http\Request;

class PlayerGameController extends Controller
{
    public function getPlayerList(Request $request)
    {
        return GameListResource::collection($this->getFilteredGameList($request));
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
        $user = $request->user();

        $playerCurrentGame = $playerGame::where('board_game_id', $request->board_game_id)
            ->where('user_id', $user->id)
            ->where('status', PlayerGame::CURRENT)->first();

        if ($playerCurrentGame) {
            $fields = $this->getFields($request);

            if ($result = $playerCurrentGame->update($fields)) {
                if ($request->type === 1) {
                    $fields = $request->validate([
                        'board_game_id' => 'required',
                    ]);

                    $boardGameItems = BoardGameItem::query()->where('slug', 'tuhlyi-banan')->first();

                    $fields['board_game_item_id'] = $boardGameItems->id;
                    $fields['user_id'] = $user->id;
                    $fields['created_by'] = $user->id;

                    BoardGameInventory::create($fields);
                }

                if ($request->type === 2) {
                    $boardGamePlayers = BoardGamePlayer::where('user_id', $user->id)->where('board_game_id', $request->board_game_id)->first();

                    $points = $boardGamePlayers->points + $playerCurrentGame->game->points;

                    $boardGamePlayers->update(['points' => $points]);
                }

                return $result;
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

    private function getFilteredGameList($request)
    {
        $user = $request->user();

        $boardGameGameQuery = BoardGameGameList::query()->where('board_game_id', $request->board_game_id);

        if ($request->platform_id) {
            $boardGameGameQuery->where('gaming_platform_id', $request->platform_id);
        }

        $boardGameGameList = $boardGameGameQuery->get();

        $playerGameList = PlayerGame::query()->where('board_game_id', $request->board_game_id)->where('user_id', $user->id)->get();

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
