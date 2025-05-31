<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Models\BoardGame\BoardGamePlayerTimer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TimerController extends Controller
{
    public function start(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $BoardGamePlayerTimer = BoardGamePlayerTimer::query()
                ->where('user_id', $user->id)
                ->where('board_game_id', $request->board_game_id)
                ->orderBy('id', 'desc')->first();

            if ($BoardGamePlayerTimer) {
                return response()->json(['error' => 'Таймер уже запущен'])->setStatusCode(Response::HTTP_OK);
            } else {
                $fields = [
                    'user_id' => $user->id,
                    'created_by' => $user->id,
                    'board_game_id' => $request->board_game_id,
                    'time_start' => Carbon::now(),
                ];

                return BoardGamePlayerTimer::create($fields);
            }
        } else {
            return response()->json(['error' => 'Пользователь не найден'])->setStatusCode(Response::HTTP_OK);
        }
    }

    public function stop(Request $request)
    {
        $user = $request->user();
    }

    public function edit(Request $request)
    {
        $user = $request->user();
    }


    public function status(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $status = [
                'active' => false,
            ];

            $BoardGamePlayerTimer = BoardGamePlayerTimer::query()
                ->where('user_id', $user->id)
                ->where('board_game_id', $request->board_game_id)
                ->orderBy('id', 'desc')->first();

            if ($BoardGamePlayerTimer) {
                $status = [
                    'active' => true,
                ];

                $diffInSeconds = Carbon::parse($BoardGamePlayerTimer->time_start)->diffInSeconds(Carbon::now());

                $status['time'] = $diffInSeconds;
            }

            return $status;
        } else {
            return response()->json(['error' => 'Пользователь не найден'])->setStatusCode(Response::HTTP_OK);
        }
    }
}
