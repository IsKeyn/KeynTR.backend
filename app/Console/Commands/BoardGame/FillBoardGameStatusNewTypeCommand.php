<?php

namespace App\Console\Commands\BoardGame;

use App\Models\BoardGame\PlayerStatusEffect;
use App\Models\BoardGame\StatusEffect;
use App\Models\BoardGame\StatusEffectBind;
use Illuminate\Console\Command;

class FillBoardGameStatusNewTypeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'board-game:fill-board-game-status-new-type-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->line('НАЧАЛО работы комманды заполнения таблицы статус эффектов');

        $statusEffects = StatusEffect::all();

        foreach ($statusEffects as $statusEffect) {
            $statusEffectBind = [
                'status_effect_id' => $statusEffect->id,
                'board_game_id' => $statusEffect->board_game_id,
                'active' => $statusEffect->active,
                'created_by' => 2,
            ];

            $createResult = StatusEffectBind::create($statusEffectBind);

            if ($createResult) {
                $playerStatusEffects = PlayerStatusEffect::query()->where('status_effect_id', $statusEffect->id)->get();

                foreach ($playerStatusEffects as $playerStatusEffect) {
                    $playerStatusEffect->status_effect_bind_id = $createResult->id;
                    $playerStatusEffect->saveQuietly();
                }
            }
        }

        $this->line('КОНЕЦ работы комманды заполнения таблицы статус эффектов');
    }
}
