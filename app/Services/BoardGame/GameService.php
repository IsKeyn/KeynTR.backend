<?php

namespace App\Services\BoardGame;

class GameService
{
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

    public static function rerollPoints($boardGame)
    {
        $subtractPointsSetting = $boardGame->settings->where('code', '=', 'subtract_points')->first();
        return $subtractPointsSetting ? $subtractPointsSetting->value : 25;
    }
}
