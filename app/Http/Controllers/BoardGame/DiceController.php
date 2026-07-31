<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Models\BoardGame\PlayerStatusEffect;
use App\Models\BoardGame\StatusEffect;
use App\Services\BoardGame\BoardService;
use App\Services\BoardGame\LogService;
use App\Services\BoardGame\PlayerGameService;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class DiceController extends Controller
{
    /**
     * @param Request $request
     * @param PlayerStatusEffect $playerStatusEffect
     * @return array|\Illuminate\Http\JsonResponse|string[]
     */
    public function rollDice(
        Request $request,
        PlayerStatusEffect $playerStatusEffect
    ) {
        $conditionData = PlayerGameService::checkConditions($request->slug);

        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        }

        if (!$conditionData['user']) {
            return response()->json(['error' => 'Требуется авторизация'])->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        $dice = $request->dice ? $request->dice : 6;

        $rollResult = null;

        $playerStatusEffects = $playerStatusEffect
            ->findByUserId($conditionData['user']->id)
            ->findByBoardGame($conditionData['boardGame']->id)
            ->with(['statusEffectBind', 'statusEffectBind.statusEffect'])
            ->active()
            ->get();

        $updateData = false;

        foreach ($playerStatusEffects as $statusEffect) {
            if ((int)$statusEffect->statusEffectBind->statusEffect->type === StatusEffect::DICE_TYPE) {
                foreach ($statusEffect->statusEffectBind->statusEffect->actions as $action) {
                    $action = (Object) $action;

                    if (isset($action->value) && $action->value) {
                        $rollResult = $action->value;
                    }
                }

                $statusEffect->update(['active' => false]);
                $updateData = true;
                break;
            }
        }

        if (!$rollResult) {
            $rollResult = rand(1, $dice);
        }

        /* Устанавливаем логи */
        LogService::addLog(
            $conditionData['user']->id,
            $conditionData['boardGame']->id,
            "бросил кубик D$dice, выпало $rollResult",
            $conditionData['player']->id
        );

        /* Изменяем позицию игрока на поле */
        if ($request->useStep) {
            $positionParams = [
                'type' => 'forward',
                'count' => $rollResult,
                'player' => $conditionData['player'],
            ];

            $positionData = BoardService::setPosition($positionParams, $conditionData);
        }

        $returnData = [
            'playerId' => $conditionData['player']->id,
            'rollResult' => $rollResult,
            'updateData' => $updateData
        ];

        if (isset($positionData)) {
            $returnData['positionData'] = $positionData;
        }

        return response()->json($returnData)->setStatusCode(Response::HTTP_OK);
    }
}
