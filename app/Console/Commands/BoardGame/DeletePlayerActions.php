<?php

namespace App\Console\Commands\BoardGame;

use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\BoardGameLog;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\PlayerGame;
use App\Models\BoardGame\PlayerStatusEffect;
use Illuminate\Console\Command;

class DeletePlayerActions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'board-game:delete-player-actions {playerId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Комманда удаляет действия игрока из ивента';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->line('НАЧАЛО работы комманды');

        $playerId = $this->argument('playerId');

        if (!$playerId) {
            $this->line('ID игрока не получен');
            return;
        }

        // Получаем игрока
        $player = BoardGamePlayer::query()
            ->findById($playerId)
            ->first();

        if (!$player) {
            $this->line("Игрок с ID {$playerId} не найден");
            return;
        }

        $this->line('Удаляем инвернтарь');
        PlayerGame::query()
            ->where('user_id', $player->user_id)
            ->where('bg_player_id', $player->id)
            ->where('board_game_id', $player->board_game_id)
            ->delete();

        $this->line('Удаляем инвентарь');
        BoardGameInventory::query()
            ->where('user_id', $player->user_id)
            ->where('bg_player_id', $player->id)
            ->where('board_game_id', $player->board_game_id)
            ->delete();

        $this->line('Удаляем статус эффекты');
        PlayerStatusEffect::query()
            ->where('user_id', $player->user_id)
            ->where('bg_player_id', $player->id)
            ->where('board_game_id', $player->board_game_id)
            ->delete();

        $this->line('Удаляем логи');
        BoardGameLog::query()
            ->where('created_by', $player->user_id)
            ->where('board_game_id', $player->board_game_id)
            ->delete();

        $this->line('КОНЕЦ работы комманды');
    }
}
