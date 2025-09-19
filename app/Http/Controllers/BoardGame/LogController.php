<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\LogResource;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGameLog;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogController extends Controller
{
    protected $model = BoardGameLog::class;

    public function add(Request $request) // TODO Старый метод, удалить когда перестанет использоваться
    {
        $newEntry = $request->validate([
            'message' => 'required|min:2',
            'board_game_id' => 'required',
        ]);

        if ($user = $request->user()) {
            $newEntry['created_by'] = $user->id;

            if ($entry = $this->model::create($newEntry)) {
                return response($entry, Response::HTTP_CREATED);
            }
        }
    }

    public function getLogListById(Request $request) // TODO Старый метод, удалить когда перестанет использоваться
    {
        $logs = BoardGameLog::query()->where('board_game_id', $request->boardGameId)->orderByDesc('id')->limit(100)->get();

        return LogResource::collection($logs);
    }

    public function getList(Request $request, $slug)
    {
        $id = BoardGame::findBySlug($slug)->value('id');

        if ($id) {
            $query = BoardGameLog::query()
                ->where('board_game_id', $id)
                ->orderByDesc('created_at');

            $result = $request->perPage ? $query->paginate($request->perPage) : $query->get();

            return LogResource::collection($result);
        }
    }

    public function getPlayerLog(Request $request, $slug, $playerName)
    {
        $id = BoardGame::findBySlug($slug)->value('id');
        $user = User::query()->where('name', $playerName)->first();

        if ($id) {
            $player = BoardGamePlayer::where('user_id', $user->id)->where('board_game_id', $id)->first();

//            dd($player->id);

            $query = BoardGameLog::query()
                ->where('board_game_id', $id)
                ->where('created_by', $player->user_id)
                ->orderByDesc('created_at');

            $result = $request->perPage ? $query->paginate($request->perPage) : $query->get();

            return LogResource::collection($result);
        }
    }
}
