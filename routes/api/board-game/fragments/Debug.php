<?php

use App\Http\Controllers\BoardGame\DebugController;
use Illuminate\Support\Facades\Route;

Route::prefix('debug/')
    ->controller(DebugController::class)
    ->middleware(['bg.check.board', 'bg.check.debug'])
    ->group(function() {
    Route::post('reset-board-cell-effects', 'resetBoardCellEffects')->name('reset-board-cell-effects');
    Route::post('set-board-position', 'setBoardPosition')->name('set-board-position');
    Route::post('add-item-to-inventory', 'addItemToInventory')->name('add-item-to-inventory');
    Route::post('set-status-effect-on-player', 'setStatusEffectOnPlayer')->name('set-status-effect-on-player');
});
