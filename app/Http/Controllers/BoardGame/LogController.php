<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\LogResource;
use App\Models\BoardGame\BoardGameLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogController extends Controller
{
    protected $model = BoardGameLog::class;

    public function add(Request $request)
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

    public function getLogListById(Request $request)
    {
        $logs = BoardGameLog::query()->where('board_game_id', $request->boardGameId)->orderByDesc('id')->get();

        return LogResource::collection($logs);
    }
}
