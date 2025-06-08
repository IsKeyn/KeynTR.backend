<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\BoardGamePlayerTimer;
use App\Models\BoardGame\PlayerGame;
use App\Models\BoardGame\Timer;
use Carbon\Carbon;

class TimerService
{
    public static function getTimerStatus($timer)
    {
        $status = [
            'active' => false,
            'time' => 0,
        ];

        if ($timer) {
            foreach ($timer->playerTimer as $key => $playerTimer) {
                if ($playerTimer->time_stop === null) {
                    $status['active'] = true;
                    $status['time'] += Carbon::parse($playerTimer->time_start)->diffInSeconds(Carbon::now());
                    break;
                } else {
                    $status['time'] += Carbon::parse($playerTimer->time_start)->diffInSeconds($playerTimer->time_stop);
                }
            }

            $status['name'] = $timer->name;
            $status['limit'] = $timer->limit;
        } else {
//                return response()->json(['error' => 'Таймер не найден'])->setStatusCode(Response::HTTP_OK);
        }

        return $status;
    }

    public static function timeInGame($playerGame)
    {
        if ($playerGame) {
            $timer = Timer::query()
                ->where('slug', 'main')
                ->where('board_game_id', $playerGame->board_game_id)
                ->where('user_id', $playerGame->user_id)
                ->first();

            if ($timer) {
                $playerTimes = BoardGamePlayerTimer::query()
                    ->where('timer_id', $timer->id)
                    ->where('time_start', '>=', $playerGame->created_at)
                    ->get();

                $time = 0;

                if ($playerTimes) {
                    foreach ($playerTimes as $playerTime) {
                        if ($playerTime->time_stop) {
                            $time += Carbon::parse($playerTime->time_start)->diffInSeconds($playerTime->time_stop);
                        } else {
                            $time += Carbon::parse($playerTime->time_start)->diffInSeconds(Carbon::now());
                        }
                    }
                }

                return $time;
            }
        }
    }
}
