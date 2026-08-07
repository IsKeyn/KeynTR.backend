<?php
namespace App\Http\Middleware\BoardGame;

use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGamePlayer;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckConditions
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'status_message' => __('auth.only_for_auth'),
            ], 401);
        }

        // Получаем slug из URL или из тела запроса
        $slug = $request->route('slug') ?: $request->input('slug');

        if (!$slug) {
            return response()->json([
                'status' => 'error',
                'status_message' => __('boardGame.not_received_slug'),
            ], 400);
        }

        $boardGame = BoardGame::findBySlug($slug)->active()->first();

        if (!$boardGame) {
            return response()->json([
                'status' => 'error',
                'status_message' => __('boardGame.not_found_or_not_active'),
            ], 404);
        }

        if ($boardGame->status === 0 || $boardGame->status === 2) {
            return response()->json([
                'status' => 'error',
                'status_message' => $boardGame->status === 0 ? __('boardGame.is_finish') : __('boardGame.coming_soon'),
            ], 400);
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
            'boardGame' => $boardGame,
            'user' => $user,
            'player' => $player,
        ]);

        return $next($request);
    }
}
