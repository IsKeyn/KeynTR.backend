<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\BoardGamePlayerResource;
use App\Http\Resources\BoardGame\PlayerGameResource;
use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\BoardGameItem;
use App\Models\BoardGame\PlayerGame;
use App\Services\BoardGame\StatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StatsController extends Controller
{
    public function get(Request $request)
    {
        $cacheKey = 'board_game_stats_cache_' . $request->board_game_id . '_' . $request->limit;
        $minutes = 1440; // 24 часа в минутах

        return Cache::remember($cacheKey, $minutes, function () use ($request) {
            $StatsService = new StatsService();

            $playerGames = PlayerGame::query()->where('board_game_id', $request->board_game_id)->get();
            $gameList = $StatsService->getGameList($playerGames);
            $limit = $request->limit ? (int)$request->limit : 5;

            /* Самая часто проходимая игра */
            $mostCompletedGames = $StatsService->getGamesListByStatus($playerGames, $gameList, PlayerGame::COMPLETED, $limit);

            /* Самая часто реролящася игра */
            $mostRerolledGames = $StatsService->getGamesListByStatus($playerGames, $gameList, PlayerGame::REROLLED, $limit);

            /* Самые быстро проходимые игры */
            $shortestGames = $StatsService->getGamesByTime($request->board_game_id, 'asc', $limit);

            /* Самые долго проходимые игры */
            $longestGames = $StatsService->getGamesByTime($request->board_game_id, 'desc', $limit);

            /* Больше всего использовано бананов */
            $bananaItem = BoardGameItem::where('slug', '=', 'tuhlyi-banan')->first();
            if ($bananaItem && $bananaId = $bananaItem->id) {
                $playerMostUseBanana = $StatsService->getUserWhoMostUseItem($bananaId, $request->board_game_id, $limit);
            }

            /* Больше всего использовано бомб */
            $bomb = BoardGameItem::where('slug', '=', 'bomb')->first();
            if ($bomb && $bombId = $bomb->id) {
                $playerMostUseBomb = $StatsService->getUserWhoMostUseItem(BoardGameItem::where('slug', '=', 'bomb')->first()->id, $request->board_game_id, $limit);
            }

            /* Активность */
            $activity = [];

            $logs = BoardGameInventory::query()
                ->where('board_game_id', $request->board_game_id)
                ->get();

            $format = 'd:m:Y';

            foreach ($logs as $log) {
                if ($log->created_at) {
                    if (isset($activity[$log->created_at->format($format)])) {
                        $activity[$log->created_at->format($format)]++;
                    } else {
                        $activity[$log->created_at->format($format)] = 1;
                    }
                }
            }

            ksort($activity);

            $returnData = [];

            if (isset($mostCompletedGames)) {
                $returnData['mostCompletedGames'] = [
                    'name' => 'Пройденные наибольшее количество раз игры',
                    'data' => PlayerGameResource::collection($mostCompletedGames)
                ];
            }

            if (isset($mostRerolledGames)) {
                $returnData['mostRerolledGames'] = [
                    'name' => 'Наиболее релолящиеся игры',
                    'data' => PlayerGameResource::collection($mostRerolledGames)
                ];
            }

            if (isset($shortestGames)) {
                $returnData['shortestGames'] = [
                    'name' => 'Наиболее короткие игры',
                    'data' => PlayerGameResource::collection($shortestGames)
                ];
            }

            if (isset($longestGames)) {
                $returnData['longestGames'] = [
                    'name' => 'Наиболее длинные игры',
                    'data' => PlayerGameResource::collection($longestGames)
                ];
            }

            if (isset($playerMostUseBanana)) {
                $returnData['maxBananaCount'] = [
                    'name' => 'По улицам пройдется и бананов обожрется...',
                    'data' => BoardGamePlayerResource::collection($playerMostUseBanana)
                ];
            }

            if (isset($playerMostUseBomb)) {
                $returnData['kirovReporting'] = [
                    'name' => 'Киров репортинг, больше всего использованных бомб',
                    'data' => BoardGamePlayerResource::collection($playerMostUseBomb)
                ];
            }

            if (isset($activity)) {
                $returnData['activity'] = [
                    'name' => 'Активность',
                    'data' => array_slice($activity, -30),
                ];
            }

            return $returnData;
        });
    }
}
