<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\BoardGamePlayerPositionsResource;
use App\Http\Resources\BoardGame\BoardGamePlayerShortResource;
use App\Http\Resources\BoardGame\BoardPositionEffectsBindResource;
use App\Http\Resources\BoardGame\BoardResource;
use App\Http\Resources\BoardGame\PlayerInteractionResource;
use App\Models\BoardGame\Board;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\BoardGamePlayerPosition;
use App\Models\BoardGame\BoardPositionEffectsBind;
use App\Models\BoardGame\PlayerInteractions;
use App\Models\Setting;
use App\Services\BoardGame\BoardService;
use App\Services\BoardGame\PlayerGameService;
use App\Services\ErrorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BoardController extends Controller
{
    public function get($slug, BoardGame $BoardGame)
    {
        if ($slug) {
            $boardGame = $BoardGame->findBySlug($slug)->first();

            if ($boardGame) {
                $boardType = Setting::query()
                    ->where('code', '=', 'board_type')
                    ->where('entity_type', '=', $boardGame->model)
                    ->where('entity_id', '=', $boardGame->id)->value('value');

                if ($boardType) {
                    $board = Board::query()->where('slug', '=', $boardType)->first();
                    $players = BoardGamePlayer::query()->findByBoardGame($boardGame->id)->active()->get();
                    $boardPositionEffectsBind = BoardPositionEffectsBind::query()->findByBoardGame($boardGame->id)->active()->get();

                    if ($board) {
                        $returnData = [
                            'board' => BoardResource::make($board),
                            'players' => BoardGamePlayerShortResource::collection($players),
                            'effects' => BoardPositionEffectsBindResource::collection($boardPositionEffectsBind),
                        ];

                        $user = Auth::user();

                        if ($user) {
                            $player = BoardGamePlayer::query()->findByBoardGame($boardGame->id)->findByUserId($user->id)->first();
                            $positionHistory = BoardGamePlayerPosition::query()
                                ->findByBoardGame($boardGame->id)
                                ->findByUserId($user->id)
                                ->where('has_use_effect', true)
                                ->get();
                            $boardInteraction = PlayerInteractions::query()
                                ->findByBoardGame($boardGame->id)
                                ->where('created_by', $user->id)
                                ->where('type', 'battleForPoints')
                                ->active()
                                ->get();

                            $returnData['current_player'] = [
                                'info' => BoardGamePlayerShortResource::make($player),
                                'position_has_use_effect' => BoardGamePlayerPositionsResource::collection($positionHistory),
                                'board_interaction' => PlayerInteractionResource::collection($boardInteraction),
                            ];
                        }

                        return $returnData;
                    }
                }
            }
        }
    }

    public function usePositionEffect(Request $request)
    {
        $conditionData = PlayerGameService::checkConditions($request->slug);

        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        } else {
            $result = null;

            if ($request->id) {
                /* Получаем информацию о эффекте позиции */
                $boardPositionEffectBind = BoardPositionEffectsBind::query()
                    ->where('id', $request->id)->first();

                $position = BoardGamePlayerPosition::query()
                    ->findByBoardGame($conditionData['boardGame']->id)
                    ->findByUserId($conditionData['user']->id)
                    ->where('position', $boardPositionEffectBind->position)
                    ->first();

                 return BoardService::activateCellEffect($boardPositionEffectBind, $position, $conditionData, $request);
            } else {
                return ErrorService::message('Не получен ID предмета инвентаря');
            }
        }
    }
}
