<?php

namespace App\Services\BoardGame;

class GameService
{
    public static function calcPoints($playerCurrentGame)
    {
        $hours = round($playerCurrentGame->game_completion_time / 60);
        $factor = $hours >= 10 ? 1 : 0.5;
        $pointsForHour = 10;

        return round(($playerCurrentGame->difficult * $factor) + ($pointsForHour * $hours));
    }

    public static function rerollPoints($boardGame)
    {
        $subtractPointsSetting = $boardGame->settings->where('code', '=', 'subtract_points')->first();
        return $subtractPointsSetting ? $subtractPointsSetting : 25;
    }
}
