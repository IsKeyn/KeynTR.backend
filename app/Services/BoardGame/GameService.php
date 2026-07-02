<?php

namespace App\Services\BoardGame;

use App\Http\Resources\BoardGame\StatusEffects\BgStatusEffectResource;
use App\Models\BoardGame\PlayerGame;
use App\Models\BoardGame\PlayerStatusEffect;
use App\Models\BoardGame\StatusEffect;

class GameService
{
    /**
     * @param $playerCurrentGame
     * @return float|int
     */
    public static function calcPoints($playerCurrentGame)
    {
        $finalPoints = 0;

        if ($playerCurrentGame->game_completion_time && $playerCurrentGame->difficult) {
            $hours = round($playerCurrentGame->game_completion_time / 60);
            $factor = $hours >= 10 ? 1 : 0.5;
            $pointsForHour = 10;

            $finalPoints = round(($playerCurrentGame->difficult * $factor) + ($pointsForHour * $hours));
        } elseif ($playerCurrentGame->points) {
            $finalPoints = $playerCurrentGame->points;
        }

        return $finalPoints;
    }

    /**
     * @param $boardGame
     * @param null $game
     * @return array
     */
    public static function rerollPenalty(
        $boardGame,
        $game = null
    )
    {
        $penaltyDefence = false;
        $pointsForReroll = 0;

        // Проверяем, что у игрока нет защиты от штрафа рерола
        $playerStatusEffects = PlayerStatusEffect::query()
            ->findByUserId($game->user_id)
            ->findByBoardGame($game->board_game_id)
            ->with([
                'statusEffectBind.statusEffect',
                'statusEffectBind.statusEffect.titleImage',
            ])
            ->active()
            ->get();

        foreach ($playerStatusEffects as $statusEffect) {
            if ((int)$statusEffect->statusEffectBind->statusEffect->type === StatusEffect::GAME_LIST_TYPE) {
                foreach (json_decode($statusEffect->statusEffectBind->statusEffect->actions) as $action) {
                    if ($action->value && $action->value === 'free-reroll') {
                        $penaltyDefence = true;
                        $data = BgStatusEffectResource::make($statusEffect->statusEffect);
                        break;
                    }
                }
            }

            if ($penaltyDefence) break;
        }

        if (!$penaltyDefence) {
            if ($game && $game->type === PlayerGame::TYPE_PURSE) {
                $points = GameService::calcPoints($game->game);
                $pointsForReroll = round(($points / 100) * 75);
            } else {
                $boardGame->load(['settings']);

                $subtractPointsSetting = $boardGame->settings->where('code', '=', 'subtract_points')->first();
                $pointsForReroll = $subtractPointsSetting ? $subtractPointsSetting->value : 25;
            }
        }

        return [
            'penaltyDefence' => $penaltyDefence,
            'pointForReroll' => $pointsForReroll,
            'data' => $data ?? null,
        ];
    }
}
