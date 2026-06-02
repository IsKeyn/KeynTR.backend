<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\TimerResource;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGamePlayerTimer;
use App\Models\BoardGame\Timer;
use App\Models\User;
use App\Services\BoardGame\LogService;
use App\Services\BoardGame\TimerService;
use App\Services\Cache\TimerCacheService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TimerController extends Controller
{
    public function start(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $boardGameId = BoardGame::findBySlug($request->boardGameSlug)->value('id');

            $timer = Timer::query()
                ->where('user_id', $user->id)
                ->where('board_game_id', $boardGameId)
                ->where('slug', $request->slug ? $request->slug : 'main')
                ->where('active', true)
                ->orderBy('id', 'desc')->first();

            if ($timer) {
                return $this->toggleTimer($user, $timer, 'start', $request);
            } else {
                if ($timer = TimerService::createTimer($boardGameId, $user, $request->slug ? $request->slug : 'main')) {
                    return $this->toggleTimer($user, $timer, 'start', $request);
                }
            }
        } else {
            return response()->json(['error' => 'Пользователь не найден'])->setStatusCode(Response::HTTP_OK);
        }
    }

    public function stop(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $boardGameId = BoardGame::findBySlug($request->boardGameSlug)->value('id');

            $timer = Timer::query()
                ->where('user_id', $user->id)
                ->where('board_game_id', $boardGameId)
                ->where('slug', $request->slug ? $request->slug : 'main')
                ->where('active', true)
                ->orderBy('id', 'desc')->first();

            if (!$timer) {
                return response()->json(['error' => 'Таймера не существует'])->setStatusCode(Response::HTTP_OK);
            } else {
                return $this->toggleTimer($user, $timer, 'stop', $request);
            }
        } else {
            return response()->json(['error' => 'Пользователь не найден'])->setStatusCode(Response::HTTP_OK);
        }
    }

    public function toggleTimer($user, $timer, $action, $request)
    {
        $BoardGamePlayerTimer = BoardGamePlayerTimer::query()
            ->where('timer_id', $timer->id)
            ->where('time_stop', null)
            ->orderBy('id', 'desc')->first();

        if ($action === 'start') {
            Cache::forget('bg_game_timer_' . $user->id . '_' . $request->boardGameSlug . '_' . $request->slug);

            if ($BoardGamePlayerTimer) {
                return response()->json(['error' => 'Таймер уже запущен'])->setStatusCode(Response::HTTP_OK);
            } else {
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
            Cache::forget('bg_game_timer_' . $user->id . '_' . $request->boardGameSlug . '_' . $request->slug);

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

    public function status(Request $request)
    {
        // Получаем пользователя максимально дешево
        $user = $request->user_id
            ? User::select('id')->find($request->user_id)
            : $request->user();

        if (!$user) {
            return response()->json(['error' => 'Пользователь не найден'], Response::HTTP_OK);
        }

        $cacheKey = 'bg_game_timer_' . $user->id . '_' . $request->boardGameSlug . '_' . $request->slug;
        $minutes = 30;

        return Cache::remember($cacheKey, $minutes, function () use ($request, $user) {
            // Легковесное получение board_game_id
            $boardGameId = BoardGame::where('slug', $request->boardGameSlug)
                ->value('id');

            if (!$boardGameId) {
                return response()->json(['error' => 'Ивент не найден'], Response::HTTP_OK);
            }

            // Получаем только один нужный таймер + связанные таймеры игрока
            $timer = Timer::with('playerTimer')
                ->select(['id', 'name', 'limit', 'user_id', 'board_game_id', 'slug', 'settings']) // только нужные поля
                ->where([
                    ['user_id', '=', $user->id],
                    ['board_game_id', '=', $boardGameId],
                    ['active', '=', true],
                    ['slug', '=', $request->slug ?: 'main'],
                ])
                ->latest('id')
                ->first();

            return TimerService::getTimerStatus($timer);
        });
    }

    public function edit(Request $request)
    {
        $result = null;
        $user = $request->user();

        if ($user) {
            $boardGameId = BoardGame::findBySlug($request->boardGameSlug)->value('id');

            $timerSlug = $request->slug ? $request->slug : 'main';

            $timer = Timer::query()
                ->where('user_id', $user->id)
                ->where('board_game_id', $boardGameId)
                ->where('slug', $timerSlug)
                ->where('active', true)
                ->orderBy('id', 'desc')->first();

            Cache::forget('bg_game_timer_' . $user->id . '_' . $request->boardGameSlug . '_' . $timerSlug);

            $boardGame = BoardGame::query()->where('id', $boardGameId)->first();

            if ($timer) {
                $status = TimerService::getTimerStatus($timer);

                if ($request->slug === 'main' && ($status['reached_the_limit'] ?? null)) {
                    return response()->json(['error' => 'Вы не можете менять значения основного таймера, когда он достиг лимита'])->setStatusCode(Response::HTTP_OK);
                }

                if (isset($status['time'])) {
                    if ($status['time'] === $request->seconds) {
                        return true;
                    }

                    if ($status['time'] > $request->seconds) {
                        $secondsForSave = $status['time'] - $request->seconds;

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
                                'created_by' => $user->id,
                            ];

                            $result = BoardGamePlayerTimer::create($fields);
                        }
                    }

                    if ($status['time'] < $request->seconds) {
                        $BoardGamePlayerTimer = BoardGamePlayerTimer::query()
                            ->where('timer_id', $timer->id)
                            ->where('time_start', '>=', Carbon::now()->subSeconds($request->seconds))
                            ->get();

                        foreach ($BoardGamePlayerTimer as $key => $playerTimer) {
                            $playerTimer->delete();
                        }

                        $timer = Timer::query()
                            ->where('user_id', $user->id)
                            ->where('board_game_id', $boardGameId)
                            ->where('slug', $request->slug ? $request->slug : 'main')
                            ->where('active', true)
                            ->orderBy('id', 'desc')->first();

                        $statusNew = TimerService::getTimerStatus($timer);

                        $secondsForSave = $request->seconds - $statusNew['time'];

                        $fields = [
                            'timer_id' => $timer->id,
                            'time_start' => Carbon::now()->subSeconds($secondsForSave),
                            'time_stop' => Carbon::now(),
                            'created_by' => $user->id,
                        ];

                        $result = BoardGamePlayerTimer::create($fields);
                    }

                    if ($request->slug === 'main') {
                        $message = 'Изменил таймер с ' .  gmdate("H:i:s", $status['time']) . ' на ' .  gmdate("H:i:s", $request->seconds);
                        LogService::addLog($user->id, $boardGameId, $message);
                    }

                    return $result;
                } else {
                    return response()->json(['error' => 'Ошибка статуса таймера'])->setStatusCode(Response::HTTP_OK);
                }
            } else {
                $timerFields = [
                    'user_id' => $user->id,
                    'board_game_id' => $boardGameId,
                    'name' => $boardGame->name,
                    'limit' => 100*60*60,
                    'slug' => $request->slug ? $request->slug : 'main',
                    'created_by' => $user->id,
                ];

                if ($timer = Timer::create($timerFields)) {
                    $fields = [
                        'timer_id' => $timer->id,
                        'time_start' => Carbon::now()->subSeconds($request->seconds),
                        'time_stop' => Carbon::now(),
                        'created_by' => $user->id,
                    ];

                    return BoardGamePlayerTimer::create($fields);
                }
//                return response()->json(['error' => 'Таймер не найден'])->setStatusCode(Response::HTTP_OK);
            }
        } else {
            return response()->json(['error' => 'Пользователь не найден'])->setStatusCode(Response::HTTP_OK);
        }
    }

    /**
     * Возвращает коллекцию таймеров текущего пользователя.
     *
     * @return ResourceCollection|null
     */
    public function list(Request $request): ?ResourceCollection
    {
        $user = $request->user_id
            ? User::select('id')->find($request->user_id)
            : $request->user();

        if (!$user) {
            return null;
        }

        $cacheKey = TimerCacheService::LIST_PREFIX . '_' . $user->id;
        $time = TimerCacheService::TIME;

        return Cache::remember($cacheKey, $time, function () use ($request, $user) {
            $boardGameId = BoardGame::findBySlug($request->boardGameSlug)->value('id');

            $timers = Timer::query()
                ->findByUserId($user->id)
                ->findByBoardGame($boardGameId)
                ->active()
                ->get();

            return TimerResource::collection($timers);
        });
    }

    public function add(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $boardGameId = BoardGame::findBySlug($request->boardGameSlug)->value('id');

            $timersCount = Timer::query()
                ->where('user_id', $user->id)
                ->where('board_game_id', $boardGameId)
                ->where('active', true)
                ->count();

            if ($timersCount >= 6) {
                return response()->json(['error' => 'Вы можете иметь не более 5 дополнительных таймеров в одном эвенте'])->setStatusCode(Response::HTTP_OK);
            }

            if ($request->name) {
                $slug = str_replace('-', '_', Str::slug($request->name));

                // main - зарезервированный под BoardGame slug
                if ($slug === 'main') {
                    return response()->json(['error' => 'Вы не можете завести таймер с таким названием, пожалуйста выберите другое название'])->setStatusCode(Response::HTTP_OK);
                }

                $timer = Timer::query()
                    ->where('user_id', $user->id)
                    ->where('board_game_id', $boardGameId)
                    ->where('slug', $slug)
                    ->where('active', true)
                    ->orderBy('id', 'desc')->first();

                if ($timer) {
                    return response()->json(['error' => 'Такой таймер уже существует'])->setStatusCode(Response::HTTP_OK);
                } else {
                    $timerFields = [
                        'user_id' => $user->id,
                        'board_game_id' => $boardGameId,
                        'name' => $request->name,
                        'description' => $request->description,
                        'slug' => $slug,
                        'created_by' => $user->id,
                    ];

                    if ($request->limit) {
                        $timerFields['limit'] = $request->limit;
                    }

                    return Timer::create($timerFields);
                }
            } else {
                return response()->json(['error' => 'Название таймера отсутствует'])->setStatusCode(Response::HTTP_OK);
            }
        } else {
            return response()->json(['error' => 'Пользователь не найден'])->setStatusCode(Response::HTTP_OK);
        }
    }

    public function delete(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $boardGameId = BoardGame::findBySlug($request->boardGameSlug)->value('id');

            $timer = Timer::query()
                ->where('user_id', $user->id)
                ->where('board_game_id', $boardGameId)
                ->where('slug', $request->slug)
                ->where('active', true)
                ->orderBy('id', 'desc')->first();

            if (!$timer) {
                return response()->json(['error' => 'Таймер не найден'])->setStatusCode(Response::HTTP_OK);
            } else {
                return $timer->delete();
            }
        } else {
            return response()->json(['error' => 'Пользователь не найден'])->setStatusCode(Response::HTTP_OK);
        }
    }

    /**
     * Обновляет настройки таймера
     *
     * @return JsonResponse
     */
    public function setSettings(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user) {
            $boardGameId = BoardGame::findBySlug($request->bgSlug)->value('id');

            $timer = Timer::query()
                ->findByUserId($user->id)
                ->findByBoardGame($boardGameId)
                ->findBySlug($request->slug)
                ->active()
                ->first();

            if (!$timer) {
                return response()->json(['error' => __('boardGame.timer_not_found')])->setStatusCode(Response::HTTP_OK);
            } else {
                if ($request->settings) {
                    $timer->settings = $request->settings;
                    $timer->save();

                    return response()->json(['status' => __('boardGame.timer_settings_updated')])->setStatusCode(Response::HTTP_OK);
                } else {
                    return response()->json(['error' => __('boardGame.timer_settings_not_received')])->setStatusCode(Response::HTTP_OK);
                }
            }
        } else {
            return response()->json(['error' => __('boardGame.user_not_found')])->setStatusCode(Response::HTTP_OK);
        }
    }
}
