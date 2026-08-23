<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\Board\BgBoardPositionEffectsBindResource;
use App\Http\Resources\BoardGame\Board\BgBoardResource;
use App\Http\Resources\BoardGame\Board\BgPlayerInteractionResource;
use App\Http\Resources\BoardGame\Board\BgPlayerPositionsResource;
use App\Http\Resources\BoardGame\Player\BgPlayerDetailResource;
use App\Models\BoardGame\Board;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\BoardGamePlayerPosition;
use App\Models\BoardGame\BoardPositionEffectsBind;
use App\Services\BoardGame\BoardService;
use App\Services\BoardGame\PlayerGameService;
use App\Services\Cache\BoardGame\BgPlayerCacheService;
use App\Services\Cache\BoardGame\BoardGameCacheService;
use App\Services\ErrorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class BoardController extends Controller
{
    /**
     * @param $slug
     * @param BoardGame $BoardGame
     * @return array|JsonResponse
     */
    public function get($slug, BoardGame $BoardGame) : array|JsonResponse
    {
        if (!$slug) {
            return response()
                ->json()
                ->setStatusCode(Response::HTTP_NOT_FOUND);
        }

        $cacheKey = BoardGameCacheService::DETAIL_PREFIX . '_' . $slug . '_board';

        $returnData1 = Cache::remember(
            $cacheKey,
            BoardGameCacheService::TIME,
            function () use ($BoardGame, $slug
            ) {
                $boardGame = $BoardGame::query()
                    ->findBySlug($slug)
                    ->with([
                        'settings',
                        'players' => function ($query) {
                            $query->active();
                        },
                        'players.user',
                        'players.user.avatar',
                        'players.user.additionalFields',
                        'players.positions' => function ($query) {
                            $query->active()->orderBy('id', 'desc');
                        },
                        'boardPositionEffectsBinds' => function ($query) {
                            $query->active();
                        },
                        'boardPositionEffectsBinds.boardPositionEffect',
                        'boardPositionEffectsBinds.boardPositionEffect.titleImage',
                    ])
                    ->first();

                if (!$boardGame) {
                    abort(404, __('boardGame.not_found'));
                }

                $boardType = $boardGame->settings->where('code', '=', 'board_type')->value('value');

                if (!$boardType) {
                    abort(404, __('boardGame.board_type_not_found'));
                }

                $board = Board::query()->where('slug', '=', $boardType)->active()->first();

                if (!$board) {
                    abort(404, __('boardGame.board.not_found'));
                }

                return [
                    'board' => BgBoardResource::make($board),
                    'players' => BgPlayerDetailResource::collection($boardGame->players),
                    'effects' => BgBoardPositionEffectsBindResource::collection($boardGame->boardPositionEffectsBinds),
                ];
        });

        $returnData2 = [];

        $user = Auth::user();

        if ($user) {
            $bgPlayerCacheKey = BgPlayerCacheService::DETAIL_PREFIX . '_' . $slug . '_' . $user->id . '_board';

            $returnData2 = Cache::remember(
                $bgPlayerCacheKey,
                BgPlayerCacheService::TIME,
                function () use ($user, $BoardGame, $slug
            ) {
                $bgId = $BoardGame::query()->findBySlug($slug)->value('id');

                $player = BoardGamePlayer::query()
                    ->where('user_id', $user->id)
                    ->findByBoardGame($bgId)
                    ->with([
                        'user',
                        'user.avatar',
                        'user.additionalFields',
                        'positions' => function ($query) {
                            $query->active()->orderBy('id', 'desc');
                        },
                        'playerPositions' => function ($query) {
                            $query->where('has_use_effect', true);
                        },
                        'playerInteractions' => function ($query) {
                            $query->where('type', 'battleForPoints')->active();
                        },
                        'playerInteractions.withPlayerData',
                        'playerInteractions.withPlayerData.avatar',
                        'playerInteractions.createdByData',
                        'playerInteractions.createdByData.avatar',
                    ])
                    ->first();

                if (!$player) {
                    return [];
                }

                return [
                    'current_player' => [
                        'info' => BgPlayerDetailResource::make($player),
                        'position_has_use_effect' => BgPlayerPositionsResource::collection($player->playerPositions),
                        'board_interaction' => BgPlayerInteractionResource::collection($player->playerInteractions),
                    ],
                ];
            });
        }

        return [
            ...$returnData1,
            ...$returnData2,
        ];
    }

    /**
     * @param Request $request
     * @return array|bool|mixed|string|null
     */
    public function usePositionEffect(Request $request)
    {
        $conditionData = PlayerGameService::checkConditions($request->slug);

        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        }

        $result = null;

        if (!$request->id) {
            return ErrorService::message('Не получен ID предмета инвентаря');
        }

        // Получаем информацию о эффекте позиции
        $boardPositionEffectBind = BoardPositionEffectsBind::query()
            ->where('id', $request->id)
            ->first();

        $position = BoardGamePlayerPosition::query()
            ->findByBoardGame($conditionData['boardGame']->id)
            ->findByUserId($conditionData['user']->id)
            ->where('position', $boardPositionEffectBind->position)
            ->first();

         return BoardService::activateCellEffect($boardPositionEffectBind, $position, $conditionData, $request);
    }
}
