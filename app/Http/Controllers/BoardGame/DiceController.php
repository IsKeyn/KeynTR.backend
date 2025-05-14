<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Models\BoardGame\PlayerStatusEffect;
use App\Models\BoardGame\StatusEffect;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class DiceController extends Controller
{
    public function rollDice(
        Request $request,
        PlayerStatusEffect $playerStatusEffect
    ) {
        $user = $request->user();

        if ($user) {
            $dice = 6;

            if ($request->dice) {
                $dice = $request->dice;
            }

            $rollResult = null;

            $playerStatusEffects = $playerStatusEffect->where('user_id', $user->id)->where('active', true)->get();

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

            return response()->json(['rollResult' => $rollResult, 'updateData' => $updateData])->setStatusCode(Response::HTTP_OK);
        } else {
            return response()->json(['error' => 'Требуется авторизация'])->setStatusCode(Response::HTTP_BAD_REQUEST);
        }
    }
}
