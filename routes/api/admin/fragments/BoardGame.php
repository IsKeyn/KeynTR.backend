<?php

use Illuminate\Support\Facades\Route;

Route::prefix('BoardGame')->group(function () {
    Route::name('board-game')->middleware(['can:bg.edit'])->group(base_path('routes/api/admin/fragments/BgFragments/BoardGame.php'));
    Route::name('player')->middleware(['can:bg.players.edit'])->group(base_path('routes/api/admin/fragments/BgFragments/Player.php'));
    Route::name('item')->middleware(['can:bg.item.edit'])->group(base_path('routes/api/admin/fragments/BgFragments/Item.php'));
    Route::name('item-bind')->middleware(['can:bg.item-bind.edit'])->group(base_path('routes/api/admin/fragments/BgFragments/ItemBind.php'));
    Route::name('inventory')->middleware(['can:bg.player-inventory.edit'])->group(base_path('routes/api/admin/fragments/BgFragments/Inventory.php'));
    Route::name('status-effect')->middleware(['can:bg.status-effect.edit'])->group(base_path('routes/api/admin/fragments/BgFragments/StatusEffect.php'));
    Route::name('status-effect-bind')->middleware(['can:bg.status-effect-bind.edit'])->group(base_path('routes/api/admin/fragments/BgFragments/StatusEffectBind.php'));
    Route::name('board')->middleware(['can:bg.board.edit'])->group(base_path('routes/api/admin/fragments/BgFragments/Board.php'));
    Route::name('board-position-effect')->middleware(['can:bg.board-position-effect.edit'])->group(base_path('routes/api/admin/fragments/BgFragments/BoardPositionEffect.php'));
    Route::name('board-position-effect-bind')->middleware(['can:bg.board-position-effects-bind.edit'])->group(base_path('routes/api/admin/fragments/BgFragments/BoardPositionEffectsBind.php'));
    Route::name('player-position')->middleware(['can:bg.player-position.edit'])->group(base_path('routes/api/admin/fragments/BgFragments/PlayerPosition.php'));
    Route::name('game-list')->middleware(['can:bg.game-list.edit'])->group(base_path('routes/api/admin/fragments/BgFragments/GameList.php'));
    Route::name('player-game')->middleware(['can:bg.player-game.edit'])->group(base_path('routes/api/admin/fragments/BgFragments/PlayerGame.php'));
    Route::name('timer')->middleware(['can:bg.timer.edit'])->group(base_path('routes/api/admin/fragments/BgFragments/Timer.php'));
    Route::name('bgPlayerTimer')->middleware(['can:bg.player-timer.edit'])->group(base_path('routes/api/admin/fragments/BgFragments/PlayerTimer.php'));
    Route::name('log')->middleware(['can:bg.log.edit'])->group(base_path('routes/api/admin/fragments/BgFragments/Log.php'));
});
