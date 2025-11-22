<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\stats\BoardGamePlayerStatsResource;
use App\Http\Resources\BoardGame\stats\ItemStatsResource;
use App\Http\Resources\BoardGame\stats\PlayerGameStatsResource;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGameLog;
use App\Models\BoardGame\Item;
use App\Models\BoardGame\PlayerGame;
use App\Services\BoardGame\StatsService;
use App\Services\ErrorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StatsController extends Controller
{
    public function get(Request $request)
    {
        $cacheKey = 'bg_stats_cache_' . $request->slug . '_' . $request->limit;
        $minutes = 1440; // 24 часа в минутах

        return Cache::remember($cacheKey, $minutes, function () use ($request) {
            $statsService = new StatsService();

            if (!$request->slug) {
                return ErrorService::message('Не получен slug');
            }

            $boardGameId = BoardGame::query()->findBySlug($request->slug)->value('id');

            if (!$boardGameId) {
                return ErrorService::message('Ивент не найден');
            }

            $playerGames = PlayerGame::query()->findByBoardGame($boardGameId)->get();
            $gameList = $statsService->getGameList($playerGames);

            $limit = $request->limit ? (int)$request->limit : 5;

            /* Самая часто проходимая игра */
            $mostCompletedGames = $statsService->getGamesListByStatus($playerGames, $gameList, PlayerGame::COMPLETED, $limit);

            /* Самая часто реролящася игра */
            $mostRerolledGames = $statsService->getGamesListByStatus($playerGames, $gameList, PlayerGame::REROLLED, $limit);

            /* Самые быстро проходимые игры */
            $shortestGames = $statsService->getGamesByTime($boardGameId, 'asc', $limit);

            /* Самые долго проходимые игры */
            $longestGames = $statsService->getGamesByTime($boardGameId, 'desc', $limit);

            /* Наиболее используемые предметы */
            $mostUsedItem = $statsService->getUsedItemsList($boardGameId, 'desc', $limit);

            /* Больше всего использовано бананов */
            $bananaItem = Item::where('slug', '=', 'tuhlyi-banan')->first();
            if ($bananaItem) {
                $playerMostUseBanana = $statsService->getUserWhoMostUseItem($bananaItem->id, $boardGameId, $limit);
            }

            /* Больше всего использовано бомб */
            $bomb = Item::where('slug', '=', 'bomb')->first();
            if ($bomb) {
                $playerMostUseBomb = $statsService->getUserWhoMostUseItem($bomb->id, $boardGameId, $limit);
            }

            /* Участники, чьи игры чаще всего проходили */
            $mostCompletedPlayers = $statsService->playerWhoGamesMostByStatus($boardGameId, 'desc', $limit, PlayerGame::COMPLETED);

            /* Участники, чьи игры чаще всего реролили */
            $mostRerolledPlayers = $statsService->playerWhoGamesMostByStatus($boardGameId, 'desc', $limit, PlayerGame::REROLLED);

            /* Участники, прошли наибольшее количество игр */
            $playersWhoMostCompleted = $statsService->playerWhoByStatus($boardGameId, 'desc', $limit, PlayerGame::COMPLETED);

            /* Участники, кто чаще всего реролили */
            $playersWhoMostRerolled = $statsService->playerWhoByStatus($boardGameId, 'desc', $limit, PlayerGame::REROLLED);

            /* Активность */
            $activity = [];

            $logs = BoardGameLog::query()
                ->where('board_game_id', $boardGameId)
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

            $returnData = [];

            if (isset($mostCompletedGames)) {
                $returnData['mostCompletedGames'] = [
                    'name' => 'Пройденные наибольшее количество раз игры',
                    'data' => PlayerGameStatsResource::collection($mostCompletedGames)->resolve()
                ];
            }

            if (isset($mostRerolledGames)) {
                $returnData['mostRerolledGames'] = [
                    'name' => 'Наиболее релолящиеся игры',
                    'data' => PlayerGameStatsResource::collection($mostRerolledGames)->resolve()
                ];
            }

            if (isset($shortestGames)) {
                $returnData['shortestGames'] = [
                    'name' => 'Наиболее короткие игры',
                    'data' => PlayerGameStatsResource::collection($shortestGames)->resolve()
                ];
            }

            if (isset($longestGames)) {
                $returnData['longestGames'] = [
                    'name' => 'Наиболее длинные игры',
                    'data' => PlayerGameStatsResource::collection($longestGames)->resolve()
                ];
            }

            if (isset($mostUsedItem)) {
                $returnData['mostUsedItem'] = [
                    'name' => 'Наиболее используемые предметы',
                    'data' => ItemStatsResource::collection($mostUsedItem)->resolve()
                ];
            }

            if (isset($playerMostUseBanana)) {
                $returnData['maxBananaCount'] = [
                    'name' => 'По улицам пройдется и бананов обожрется...',
                    'data' => BoardGamePlayerStatsResource::collection($playerMostUseBanana)->resolve()
                ];
            }

            if (isset($playerMostUseBomb)) {
                $returnData['kirovReporting'] = [
                    'name' => 'Киров репортинг, больше всего использованных бомб',
                    'data' => BoardGamePlayerStatsResource::collection($playerMostUseBomb)->resolve()
                ];
            }

            if (isset($playersWhoMostCompleted)) {
                $returnData['playersWhoMostCompleted'] = [
                    'name' => 'Участники, прошли наибольшее количество игр',
                    'data' => BoardGamePlayerStatsResource::collection($playersWhoMostCompleted)->resolve()
                ];
            }

            if (isset($playersWhoMostRerolled)) {
                $returnData['playersWhoMostRerolled'] = [
                    'name' => 'Участники, которые реролили игры наибольшее количество раз',
                    'data' => BoardGamePlayerStatsResource::collection($playersWhoMostRerolled)->resolve()
                ];
            }

            if (isset($mostCompletedPlayers)) {
                $returnData['mostCompletedPlayers'] = [
                    'name' => 'Участники, чьи игры проходили чаще всего',
                    'data' => BoardGamePlayerStatsResource::collection($mostCompletedPlayers)->resolve()
                ];
            }

            if (isset($mostRerolledPlayers)) {
                $returnData['mostRerolledPlayers'] = [
                    'name' => 'Участники, чьи игры реролили чаще всего',
                    'data' => BoardGamePlayerStatsResource::collection($mostRerolledPlayers)->resolve()
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
