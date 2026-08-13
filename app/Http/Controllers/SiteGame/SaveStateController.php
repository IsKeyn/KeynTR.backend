<?php

namespace App\Http\Controllers\SiteGame;

use App\Http\Controllers\Controller;
use App\Models\BoardGame\BoardGamePlayerPosition;
use App\Services\SiteGame\SaveStateService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SaveStateController extends Controller
{
    public function get(
        SaveStateService $saveStateService,
        Request $request
    )
    {
        return $saveStateService->get($request->playerId, $request->entityType, $request->entityId);
    }

    public function getByBgPlayer(
        SaveStateService $saveStateService,
        Request $request
    )
    {
        if (!$request->playerId) {
            abort(Response::HTTP_BAD_REQUEST, __('siteGame.player_id_not_received'));
        }

        if (!$request->entityType) {
            abort(Response::HTTP_BAD_REQUEST, __('siteGame.entity_type_not_received'));
        }

        if (BoardGamePlayerPosition::class === 'App\Models\BoardGame\BoardGamePlayerPosition') {
            $positionId = BoardGamePlayerPosition::query()
                ->where('bg_player_id', $request->playerId)
                ->orderBy('id', 'desc')
                ->value('id');
        }


        return $saveStateService->get($request->playerId, $request->entityType, $positionId);
    }
}
