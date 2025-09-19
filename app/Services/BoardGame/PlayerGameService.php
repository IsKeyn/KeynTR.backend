<?php

namespace App\Services\BoardGame;

use App\Http\Resources\BoardGame\PlayerGameShortResource;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\PlayerGame;
use Illuminate\Support\Facades\Auth;

class PlayerGameService
{
    public static function actionsWithGame($gameListGameId, $boardGameId)
    {
        $playerGame = PlayerGame::query()
            ->where('board_game_game_list_id', $gameListGameId)
            ->where('board_game_id', $boardGameId)
            ->where('status', '!=',PlayerGame::CURRENT)
            ->get();

        return $playerGame;
    }

    public static function checkConditions($slug)
    {
        $user = Auth::user();

        if (!$user) {
            return [
                'status' => 'error',
                'status_message' => 'Функционал доступен только авторизованному пользователю',
            ];
        }

        $boardGame = BoardGame::findBySlug($slug)->active()->first();

        if (!$boardGame) {
            return [
                'status' => 'error',
                'status_message' => 'Ивент не найден или не активен',
            ];
        }

        if ($boardGame->status === 0 || $boardGame->status === 2) {
            return [
                'status' => 'error',
                'status_message' => $boardGame->status === 0 ? 'Ивент закончился' : 'Ивент скоро начнется',
            ];
        }

        $player = BoardGamePlayer::where('user_id', $user->id)->where('board_game_id', $boardGame->id)->first();

        if (!$player) {
            return [
                'status' => 'error',
                'status_message' => 'Вы должны участвовать в этом ивенте, чтобы иметь доступ к данному функционалу',
            ];
        }

        if (!$player->active) {
            return [
                'status' => 'error',
                'status_message' => 'Вы должны быть активным участником ивента, причина не активности: ' . $player->not_active_reason,
            ];
        }

        return [
            'boardGame' => $boardGame,
            'user' => $user,
            'player' => $player,
        ];
    }
}
