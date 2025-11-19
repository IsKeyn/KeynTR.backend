<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\BoardGamePlayerResource;
use App\Http\Resources\BoardGame\ItemResource;
use App\Http\Resources\BoardGame\PlayerGameResource;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\Item;
use App\Models\BoardGame\ItemBind;
use App\Models\BoardGame\PlayerGame;
use App\Services\BoardGame\StatsService;
use App\Services\ErrorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StatsController extends Controller
{
    public function get(Request $request)
    {
        $cacheKey = 'board_game_stats_cache_' . $request->slug . '_' . $request->limit;
        $minutes = 1440; // 24 часа в минутах

        return Cache::remember($cacheKey, $minutes, function () use ($request) {
            $StatsService = new StatsService();

            if (!$request->slug) {
                return ErrorService::message('Не получен slug');
            }

            $boardGame = BoardGame::query()->findBySlug($request->slug)->first();

            if (!$boardGame) {
                return ErrorService::message('Ивент не найден');
            }

            $playerGames = PlayerGame::query()->where('board_game_id', $boardGame->id)->get();
            $gameList = $StatsService->getGameList($playerGames);
            $limit = $request->limit ? (int)$request->limit : 5;

            /* Самая часто проходимая игра */
            $mostCompletedGames = $StatsService->getGamesListByStatus($playerGames, $gameList, PlayerGame::COMPLETED, $limit);

            /* Самая часто реролящася игра */
            $mostRerolledGames = $StatsService->getGamesListByStatus($playerGames, $gameList, PlayerGame::REROLLED, $limit);

            /* Самые быстро проходимые игры */
            $shortestGames = $StatsService->getGamesByTime($boardGame->id, 'asc', $limit);

            /* Самые долго проходимые игры */
            $longestGames = $StatsService->getGamesByTime($boardGame->id, 'desc', $limit);

            /* Наиболее используемые предметы */
            $mostUsedItem = $StatsService->getUsedItemsList($boardGame->id, 'desc', $limit);

            /* Больше всего использовано бананов */
            $bananaItem = Item::where('slug', '=', 'tuhlyi-banan')->first();
            if ($bananaItem) {
                $playerMostUseBanana = $StatsService->getUserWhoMostUseItem($bananaItem->id, $boardGame->id, $limit);
            }

            /* Больше всего использовано бомб */
            $bomb = Item::where('slug', '=', 'bomb')->first();
            if ($bomb) {
                $playerMostUseBomb = $StatsService->getUserWhoMostUseItem($bomb->id, $boardGame->id, $limit);
            }

            /* Участники, чьи игры чаще всего проходили */
            $mostCompletedPlayers = $StatsService->playerWhoGamesMostByStatus($boardGame->id, 'desc', $limit, PlayerGame::COMPLETED);

            /* Участники, чьи игры чаще всего реролили */
            $mostRerolledPlayers = $StatsService->playerWhoGamesMostByStatus($boardGame->id, 'desc', $limit, PlayerGame::REROLLED);

            /* Участники, прошли наибольшее количество игр */
            $playersWhoMostCompleted = $StatsService->playerWhoByStatus($boardGame->id, 'desc', $limit, PlayerGame::COMPLETED);

            /* Участники, кто чаще всего реролили */
            $playersWhoMostRerolled = $StatsService->playerWhoByStatus($boardGame->id, 'desc', $limit, PlayerGame::REROLLED);

            /* Активность */
            $activity = [];

            $logs = BoardGameInventory::query()
                ->where('board_game_id', $boardGame->id)
                ->get();

            $format = 'd.m.Y';

            foreach ($logs as $log) {
                if ($log->created_at) {
                    if (isset($activity[$log->created_at->format($format)])) {
                        $activity[$log->created_at->format($format)]++;
                    } else {
                        $activity[$log->created_at->format($format)] = 1;
                    }
                }
            }

//            ksort($activity);

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

            if (isset($mostUsedItem)) {
                $returnData['mostUsedItem'] = [
                    'name' => 'Наиболее используемые предметы',
                    'data' => ItemResource::collection($mostUsedItem)
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

            if (isset($playersWhoMostCompleted)) {
                $returnData['playersWhoMostCompleted'] = [
                    'name' => 'Участники, прошли наибольшее количество игр',
                    'data' => BoardGamePlayerResource::collection($playersWhoMostCompleted)
                ];
            }

            if (isset($playersWhoMostRerolled)) {
                $returnData['playersWhoMostRerolled'] = [
                    'name' => 'Участники, которые реролили игры наибольшее количество раз',
                    'data' => BoardGamePlayerResource::collection($playersWhoMostRerolled)
                ];
            }

            if (isset($mostCompletedPlayers)) {
                $returnData['mostCompletedPlayers'] = [
                    'name' => 'Участники, чьи игры проходили чаще всего',
                    'data' => BoardGamePlayerResource::collection($mostCompletedPlayers)
                ];
            }

            if (isset($mostRerolledPlayers)) {
                $returnData['mostRerolledPlayers'] = [
                    'name' => 'Участники, чьи игры реролили чаще всего',
                    'data' => BoardGamePlayerResource::collection($mostRerolledPlayers)
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
