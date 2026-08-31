<?php
namespace App\Http\Middleware\BoardGame;

use App\Models\BoardGame\BoardGamePlayer;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckIsPlayer
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

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'status_message' => __('auth.only_for_auth'),
            ], 401);
        }

        $player = BoardGamePlayer::where('user_id', $user->id)
            ->where('board_game_id', $boardGame->id)
            ->with('mainTimers')
            ->first();

        if (!$player) {
            return response()->json([
                'status' => 'error',
                'status_message' => __('boardGame.player.you_must_participate_in_event'),
            ], 403);
        }

        if (!$player->active) {
            return response()->json([
                'status' => 'error',
                'status_message' => __('boardGame.player.you_must_be_active_player', [
                    'message' => $player->not_active_reason,
                ]),
            ], 403);
        }

        $request->attributes->add([
            'user' => $user,
            'player' => $player,
        ]);

        return $next($request);
    }
}
