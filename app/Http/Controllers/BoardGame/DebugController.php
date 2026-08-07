<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Services\BoardGame\BgPlayerPositionService;
use App\Services\BoardGame\ItemService;
use App\Services\BoardGame\StatusEffectService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DebugController extends Controller
{
    /**
     * Устанавливает все позиции участника на игровом поле как не использованные
     *
     * @param Request $request
     * @return mixed
     */
    public function resetBoardCellEffects(Request $request)
    {
       $bgPlayerPositionService = app(BgPlayerPositionService::class);
       return $bgPlayerPositionService->resetBoardCellEffects($request->player);
    }

    /**
     * Устанавливаем позицию участника на игровом поле
     *
     * @param Request $request
     * @return array|string[]
     */
    public function setBoardPosition(Request $request)
    {
       if (!$request->position) {
           abort(Response::HTTP_BAD_REQUEST, __('boardGame.board.position_not_received'));
       }

       $bgPlayerPositionService = app(BgPlayerPositionService::class);

       return $bgPlayerPositionService->setPlayerPosition(
           [
               'boardGame' => $request->attributes->get('boardGame'),
               'player' => $request->attributes->get('player'),
               'user' => $request->attributes->get('user'),
           ],
           $request->position
       );
    }

    /**
     * Добавление предмета в инвентарь игрока
     *
     * @param Request $request
     * @return mixed
     */
    public function addItemToInventory(Request $request)
    {
        return ItemService::addToInventory(
            $request->attributes->get('user')->id,
            $request->attributes->get('boardGame')->id,
            $request->itemId,
            $request->attributes->get('player')->id,
        );
    }

    public function setStatusEffectOnPlayer(Request $request)
    {
        $statusEffectService = app(StatusEffectService::class);

        return $statusEffectService->setStatusEffect(
            [
                'user' => $request->attributes->get('user'),
                'boardGame' => $request->attributes->get('boardGame'),
                'player' => $request->attributes->get('player'),
            ],
            $request->itemId,
        );
    }
}
