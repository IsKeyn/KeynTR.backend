<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGamePlayerTimer;
use App\Models\BoardGame\Timer;
use App\Services\Entity\EntityService;
use Carbon\Carbon;
use Illuminate\Http\Response;

class TimerService
{
    public static function createTimer($boardGameId, $user, $slug = 'main')
    {
        $boardGame = BoardGame::query()->where('id', $boardGameId)->first();

        $limit = 100*60*60;

        if ($bgTimeLimit = $boardGame->settings->where('code', '=', 'time_limit')->first()) {
            $limit = $bgTimeLimit->value * 60 * 60;
        }

        $timerFields = [
            'user_id' => $user->id,
            'board_game_id' => $boardGameId,
            'name' => $boardGame->name,
            'limit' => $limit,
            'slug' => $slug,
            'created_by' => $user->id,
        ];

        return Timer::create($timerFields);
    }

    public static function getTimerStatus($timer)
    {
        if (!$timer) {
            return [
                'active' => false,
                'time' => 0,
            ];
        }

        $now = Carbon::now();
        $status = [
            'active' => false,
            'time' => 0,
            'name' => $timer->name,
            'limit' => $timer->limit,
            'reached_the_limit' => false,
            'settings' => $timer->settings,
        ];

        foreach ($timer->playerTimer as $playerTimer) {
            $start = Carbon::parse($playerTimer->time_start);

            // Таймер активен
            if ($playerTimer->time_stop === null) {
                $status['active'] = true;
                $status['time'] += $start->diffInSeconds($now);
                break;
            }

            // Таймер завершён
            $stop = Carbon::parse($playerTimer->time_stop);
            $status['time'] += $start->diffInSeconds($stop);
        }

        // Проверка лимита
        if ($status['limit']) {
            $status['reached_the_limit'] = $status['time'] >= $status['limit'];
        }

        // Автоостановка, если активен и достиг лимита
        if ($status['active'] && $status['reached_the_limit']) {
            $lastTimer = $timer->playerTimer->last();

            if ($lastTimer) {
                $lastTimer->update(['time_stop' => $now]);
                LogService::addLog(
                    1,
                    $timer->board_game_id,
                    'Таймер был остановлен, так как достиг лимита',
                );
            }

            // После остановки обновляем активность
            $status['active'] = false;
        }

        return $status;
    }

    public function toggleTimer($user, $timer, $action, $request)
    {
        $BoardGamePlayerTimer = BoardGamePlayerTimer::query()
            ->where('timer_id', $timer->id)
            ->where('time_stop', null)
            ->orderBy('id', 'desc')->first();

        if ($action === 'start') {
            if ($BoardGamePlayerTimer) {
                return response()->json(['error' => 'Таймер уже запущен'])->setStatusCode(Response::HTTP_OK);
            } else {
                if ($timer->slug === 'main') {
                    $status = $this->getTimerStatus($timer);

                    if ($status && ($status['reached_the_limit'] ?? null)) {
                        return response()->json(['error' => 'Вы не можете запускать основной таймер, когда он достиг лимита'])->setStatusCode(Response::HTTP_OK);
                    }
                }

                $fields = [
                    'timer_id' => $timer->id,
                    'time_start' => Carbon::now(),
                    'created_by' => $user->id,
                ];

                if (BoardGamePlayerTimer::create($fields)) {
                    return $this->status($request);
                }
            }
        }

        if ($action === 'stop') {
            if ($BoardGamePlayerTimer) {
                $fields = [
                    'time_stop' => Carbon::now(),
                ];

                if ($BoardGamePlayerTimer->update($fields)) {
                    return $this->status($request);
                }
            } else {
                return response()->json(['error' => 'Таймер уже остановлен'])->setStatusCode(Response::HTTP_OK);
            }
        }
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
                /*
                 * Используется именно updated_at так как например игра может быть передана мошной или обменена
                 * и время должно считаться от момента, когда игра изменила пользователя
                 */
                $playerTimes = BoardGamePlayerTimer::query()
                    ->where('timer_id', $timer->id)
                    ->where('time_start', '>=', $playerGame->updated_at)
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

    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            Timer::class,
            'App\Services\Cache\TimerCacheService',
            'App\Http\Resources\Admin\BoardGame\Timer\DetailResource',
            $id,
            [],
            $forceRefresh,
            $withTrashed,
        );
    }

    public static function reset($boardGame, $player, $timer)
    {
        $seconds = 0;

        if ($timer) {
            $status = TimerService::getTimerStatus($timer);

            if (isset($status['time'])) {
                if ($status['time'] === $seconds) {
                    return true;
                }

                if ($status['time'] > $seconds) {
                    $secondsForSave = $status['time'] - $seconds;

                    $BoardGamePlayerTimer = BoardGamePlayerTimer::query()
                        ->where('timer_id', $timer->id)
                        ->get();

                    $lastTime = null;

                    foreach ($BoardGamePlayerTimer as $key => $playerTimer) {
                        $seconds = Carbon::parse($playerTimer->time_start)->diffInSeconds($playerTimer->time_stop);

                        if ($seconds < $secondsForSave) {
                            $secondsForSave = $secondsForSave - $seconds;
                            $playerTimer->delete();
                        } else {
                            $lastTime = $playerTimer;
                        }
                    }

                    if ($lastTime) {
                        $lastTime->time_stop = Carbon::parse($lastTime->time_stop)->subSeconds($secondsForSave);

                        if ($lastTime->update()) {
                            $result = true;
                        }
                    } else {
                        $fields = [
                            'timer_id' => $timer->id,
                            'time_start' => Carbon::now()->subSeconds($secondsForSave),
                            'time_stop' => Carbon::now(),
                            'created_by' => $player->user_id,
                        ];

                        $result = BoardGamePlayerTimer::create($fields);
                    }
                }

                if ($status['time'] < $seconds) {
                    $BoardGamePlayerTimer = BoardGamePlayerTimer::query()
                        ->where('timer_id', $timer->id)
                        ->where('time_start', '>=', Carbon::now()->subSeconds($seconds))
                        ->get();

                    foreach ($BoardGamePlayerTimer as $key => $playerTimer) {
                        $playerTimer->delete();
                    }

                    $timer = Timer::query()
                        ->where('user_id', $player->user_id)
                        ->where('board_game_id', $boardGame->id)
                        ->where('slug', $boardGame->slug ? $boardGame->slug : 'main')
                        ->where('active', true)
                        ->orderBy('id', 'desc')->first();

                    $statusNew = TimerService::getTimerStatus($timer);

                    $secondsForSave = $seconds - $statusNew['time'];

                    $fields = [
                        'timer_id' => $timer->id,
                        'time_start' => Carbon::now()->subSeconds($secondsForSave),
                        'time_stop' => Carbon::now(),
                        'created_by' => $player->user_id,
                    ];

                    $result = BoardGamePlayerTimer::create($fields);
                }

                return $result;
            } else {
                return response()->json(['error' => 'Ошибка статуса таймера'])->setStatusCode(Response::HTTP_OK);
            }
        }
    }
}
