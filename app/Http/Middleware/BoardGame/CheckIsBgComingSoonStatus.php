<?php
namespace App\Http\Middleware\BoardGame;

use App\Models\BoardGame\BoardGame;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIsBgComingSoonStatus
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

        if ($boardGame->status !== BoardGame::COMING_SOON) {
            return response()->json([
                'status' => 'error',
                'status_message' => __('boardGame.coming_dont_soon'),
            ], 400);
        }

        return $next($request);
    }
}
