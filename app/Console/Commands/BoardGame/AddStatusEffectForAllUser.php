<?php

namespace App\Console\Commands\BoardGame;

use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\PlayerStatusEffect;
use App\Models\BoardGame\StatusEffect;
use App\Models\User;
use Illuminate\Console\Command;

class AddStatusEffectForAllUser extends Command
{
    protected $signature = 'board-game:AddStatusEffectForAllUser {boardGameId} {statusEffectId}';
    protected $description = 'Команда добавляет всем участникам статус эффект';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->line('НАЧАЛО работы комманды');

        $boardGameId = $this->argument('boardGameId');

        // Проверяем существование настольной игры
        $boardGame = BoardGame::findById($boardGameId)->first();

        if (!$boardGame) {
            $this->line('Не найдено настольной игры с ID: ' . $boardGameId);
        }

        // Проверяем существование статус эффект
        $statusEffectId = $this->argument('statusEffectId');

        $statusEffect = StatusEffect::findById($statusEffectId)->first();

        if (!$statusEffect) {
            $this->line('Не найдено статус эффекта с ID: ' . $boardGameId);
        }

        // Получаем активных игроков настольной игры
        $boardGamePlayers = BoardGamePlayer::findByBoardGame($boardGameId)->active()->with('user')->get();

        foreach ($boardGamePlayers as $player) {
            $playerStatusEffectFields = [
                'user_id' => $player->user_id,
                'board_game_id' => $boardGameId,
                'status_effect_id' => $statusEffectId,
                'active' => true,
                'created_by' => User::query()->where('is_admin', true)->value('id'),
            ];

            if (PlayerStatusEffect::create($playerStatusEffectFields)) {
                $this->line('Игрок ' . $player->user->name . ' получил статус эффект ' . $statusEffect->name);
            }
        }

        $this->line('КОНЕЦ работы комманды');
    }
}
