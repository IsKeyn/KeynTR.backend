<?php

namespace App\Console\Commands\BoardGame;

use App\Models\BoardGame\BoardPositionEffectsBind;
use App\Models\BoardGame\StatusEffectBind;
use App\Models\Setting;
use Illuminate\Console\Command;
use App\Models\BoardGame\BoardGameGameList;
use App\Models\BoardGame\ItemBind;

class FillBoardGame extends Command
{
    protected $signature = 'board-game:fill-board-game {boardGameIdSource} {boardGameIdForFill}';
    protected $description = 'Заполняет ивент, данными из другого ивента. Копируемые данные, список игры, предметов, статус эффектов, доска';

    public function handle()
    {
        // Заполнение игр
        $boardGameGameList = BoardGameGameList::findByBoardGame($this->argument('boardGameIdSource'))->get();

        foreach ($boardGameGameList as $original) {
            $copy = $original->replicate()->fill([
                'board_game_id' => $this->argument('boardGameIdForFill'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $copy->save();
        }

        // Заполнение предметов
        $bgItemsBinds = ItemBind::findByBoardGame($this->argument('boardGameIdSource'))->get();

        foreach ($bgItemsBinds as $original) {
            $copy = $original->replicate()->fill([
                'board_game_id' => $this->argument('boardGameIdForFill'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $copy->save();
        }

        // Заполнение статус эффектов
        $statusEffectBind = StatusEffectBind::findByBoardGame($this->argument('boardGameIdSource'))->get();

        foreach ($statusEffectBind as $original) {
            $copy = $original->replicate()->fill([
                'board_game_id' => $this->argument('boardGameIdForFill'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $copy->save();
        }

        // Заполнение эффектов доски
        $boardPositionEffectsBinds = BoardPositionEffectsBind::findByBoardGame($this->argument('boardGameIdSource'))->get();

        foreach ($boardPositionEffectsBinds as $original) {
            $copy = $original->replicate()->fill([
                'board_game_id' => $this->argument('boardGameIdForFill'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $copy->save();
        }

        // Заполнение настроек
        $settings = Setting::query()
            ->where('entity_type', 'App\Models\BoardGame\BoardGame')
            ->where('entity_id', $this->argument('boardGameIdSource'))->get();

        foreach ($settings as $original) {
            $copy = $original->replicate()->fill([
                'entity_id' => $this->argument('boardGameIdForFill'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $copy->save();
        }
    }
}
