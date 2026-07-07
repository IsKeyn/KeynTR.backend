<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\LogResource;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGameLog;
use App\Models\User;
use App\Services\Cache\BoardGame\BgLogCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
                ->with([
                    'user',
                    'user.avatar',
                ])
                ->orderByDesc('created_at')
                ->orderByDesc('id');

            $result = $request->perPage ? $query->paginate($request->perPage) : $query->get();

            return LogResource::collection($result);
        }
    }

    /*
     * Получение лога игрока
     */
    public function getPlayerLog(
        Request $request,
        $slug,
        $name,
        BoardGame $BoardGame
    )
    {
        $userId = User::findByName($name)->value('id');
        if (!$userId) return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);

        $bgId = $BoardGame->findBySlug($slug)->value('id');
        if (!$bgId) return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);

        $cacheKey = BgLogCacheService::LIST_PREFIX . '_' . $slug . '_' . $userId;

        return Cache::remember($cacheKey, BgLogCacheService::TIME, function () use ($request, $userId, $bgId) {
            $query = BoardGameLog::query()
                ->where('board_game_id', $bgId)
                ->where('created_by', $userId)
                ->orderByDesc('created_at');

            $result = $request->perPage ? $query->paginate($request->perPage) : $query->get();

            return LogResource::collection($result);
        });
    }
}
