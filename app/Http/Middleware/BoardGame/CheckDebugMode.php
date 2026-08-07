<?php

namespace App\Http\Middleware\BoardGame;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDebugMode
{
    public function handle(Request $request, Closure $next): Response
    {
        $boardGame = $request->attributes->get('boardGame');

        // Защита от некорректного применения мидлвара в роутах
        if (!$boardGame) {
            return response()->json([
                'status' => 'error',
                'status_message' => __('middleware.boardGame.dont_found_board_game_use_check_board_game_conditions_before'),
            ], 500);
        }

        $debugMode = $boardGame->settings->where('code', '=', 'debug_mode')->first();

        if (!$debugMode || !$debugMode->value) {
            return response()->json([
                'status' => 'error',
                'status_message' => __('boardGame.setting.debug_disable'),
            ], 400);
        }

        return $next($request);
    }
}
