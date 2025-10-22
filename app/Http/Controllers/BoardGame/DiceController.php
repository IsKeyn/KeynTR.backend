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
    public function rollDice(
        Request $request,
        PlayerStatusEffect $playerStatusEffect
    ) {
        $conditionData = PlayerGameService::checkConditions($request->slug);

        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        } else {
            if ($conditionData['user']) {
                $dice = 6;

                if ($request->dice) {
                    $dice = $request->dice;
                }

                $rollResult = null;

                $playerStatusEffects = $playerStatusEffect->where('user_id', $conditionData['user']->id)->where('active', true)->get();

                $updateData = false;

                foreach ($playerStatusEffects as $statusEffect) {
                    if ($statusEffect->statusEffect->type === StatusEffect::DICE_TYPE) {
                        foreach (json_decode($statusEffect->statusEffect->actions) as $action) {
                            if ($action->value) {
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
                $logMessage = "бросил кубик D$dice, выпало $rollResult";

                LogService::addLog(
                    $conditionData['user']->id,
                    $conditionData['boardGame']->id,
                    $logMessage
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
                    'rollResult' => $rollResult,
                    'updateData' => $updateData
                ];

                if (isset($positionData)) {
                    $returnData['positionData'] = $positionData;
                }

                return response()->json($returnData)->setStatusCode(Response::HTTP_OK);
            } else {
                return response()->json(['error' => 'Требуется авторизация'])->setStatusCode(Response::HTTP_BAD_REQUEST);
            }
        }
    }
}
