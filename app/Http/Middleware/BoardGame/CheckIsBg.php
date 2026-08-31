<?php
namespace App\Http\Middleware\BoardGame;

use App\Models\BoardGame\BoardGame;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIsBg
{
    public function handle(Request $request, Closure $next): Response
    {
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

        $request->attributes->add(['boardGame' => $boardGame]);

        return $next($request);
    }
}
