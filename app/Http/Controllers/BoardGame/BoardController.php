<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\BoardGamePlayerShortResource;
use App\Http\Resources\BoardGame\BoardPositionEffectsBindResource;
use App\Http\Resources\BoardGame\BoardResource;
use App\Models\BoardGame\Board;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\BoardPositionEffectsBind;
use App\Models\Setting;
use Illuminate\Http\Request;

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
                    $boardPositionEffectsBind =BoardPositionEffectsBind::query()->findByBoardGame($boardGame->id)->active()->get();

                    if ($board) {
                        return [
                            'board' => BoardResource::make($board),
                            'players' => BoardGamePlayerShortResource::collection($players),
                            'effects' => BoardPositionEffectsBindResource::collection($boardPositionEffectsBind),
                        ];
                    }
                }
            }
        }
    }
}
