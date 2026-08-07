<?php

namespace App\Services\BoardGame;

use App\Http\Resources\BoardGame\StatusEffects\BgStatusEffectResource;
use App\Models\BoardGame\BoardGamePlayer;
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
        $playerCurrentGame->load('boardGame.settings');

        $finalPoints = 0;

        if ($playerCurrentGame->game_completion_time && $playerCurrentGame->difficult) {
            $hours = round($playerCurrentGame->game_completion_time / 60);

            $factor = 1;

            if ($factorHours = (Int) $playerCurrentGame->boardGame->settings->where('code', 'factorHours')->value('value')) {
                $factor = $hours >= $factorHours ? 1 : 0.5;
            }

            $pointsForHour = 10;
            $platformDifficult = 0;

            if ((bool) $playerCurrentGame->boardGame->settings->where('code', 'usePlatformDifficultInCalc')->value('value')) {
                $platforms = $playerCurrentGame->boardGame->settings->where('code',
                    'eventGamePlatforms')->value('value');

                if ($platforms) {
                    $foundPlatform = array_filter(json_decode($platforms), function ($item) use ($playerCurrentGame) {
                        return $item->id == $playerCurrentGame->gaming_platform_id;
                    });

                    $result = reset($foundPlatform);

                    if ($result->difficult) {
                        $platformDifficult = $result->difficult;
                    }
                }
            }

            $finalPoints = round(($playerCurrentGame->difficult * $factor) + ($pointsForHour * $hours));

            if ($platformDifficult) {
                $finalPoints = round($platformDifficult * $factor) + $finalPoints;
            }
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
                foreach ($statusEffect->statusEffectBind->statusEffect->actions as $action) {
                    $action = (Object) $action;

                    if (isset($action->value) && $action->value === 'free-reroll') {
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

    /**
     * @param BoardGamePlayer $player
     * @param $currentGame
     * @return float|int
     */
    public static function finishPoints(
        BoardGamePlayer $player,
        $currentGame,
        bool $removeSe = false
    )
    {
        if (!$player || !$currentGame) return;

        // Рассчитываем количество очков за игру
        $pointsForGame = self::calcPoints($currentGame->game);

        if ($currentGame->type === PlayerGame::TYPE_TAKEN) {
            $pointsForGame = round($pointsForGame / 2);
        }


        /**
         * Добавляем или отнимаем очки за статус эффекты, деактивируем только статус эффекты,
         * у которых есть модификатор финальных очков. И только если $removeSe === true
         */
        foreach ($player->statusEffects as $playerStatusEffect) {
            $statusEffect = $playerStatusEffect->statusEffectBind->statusEffect;

            if ((int) $statusEffect->type === StatusEffect::GAME_LIST_TYPE) {
                $actions = $statusEffect->actions;
                foreach ($actions as $action) {
                    if (
                        is_array($action) &&
                        isset($action['type']) &&
                        $action['type'] === 'finalPointsMod' &&
                        isset($action['value'])
                    ) {
                        $pointsForGame = $pointsForGame * (1 + $action['value'] / 100);

                        if ($removeSe && $playerStatusEffect->active === true) {
                            $playerStatusEffect->update(['active' => false]);
                        }
                    }
                }
            }
        }

        // Отнимаем очки, за исключенные платформы
        if ((bool) $currentGame->boardGame->settings->where('code', 'hasExceptionPlatforms')->value('value')) {
            if ($player->settings
                && isset($player->settings['exceptionPlatforms'])
                && $player->settings['exceptionPlatforms']
            ) {
                if ($exceptionPlatformsCount = count($player->settings['exceptionPlatforms']) > 1) {
                    $percentForEp = ($exceptionPlatformsCount - 1) * 10;

                    if ($percentForEp) {
                        $pointsForGame = $pointsForGame * (1 - $percentForEp / 100);
                    }
                }
            }
        }

        // Добавляем очки за стрик
        $pointsForGame = round($player->streak > 0 ? $pointsForGame + ($pointsForGame / 100 * ($player->streak * 2)) : $pointsForGame);

        return $pointsForGame;
    }
}
