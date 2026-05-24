<?php

use App\Http\Controllers\Admin\BoardGame\BgPlayerTimerController;
use App\Http\Controllers\Admin\BoardGame\TimerController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BoardGame\ItemBindController;
use App\Http\Controllers\Admin\BoardGame\ItemController;
use App\Http\Controllers\Admin\BoardGame\BoardGameController;
use App\Http\Controllers\BoardGame\BoardGameInventoryController;

Route::prefix('BoardGame')->group(function () {
    Route::resource('BoardGame', BoardGameController::class)->middleware(['can:bg.edit']);
    Route::resource('Item', ItemController::class)->middleware(['can:bg.item.edit']);
    Route::resource('ItemBind', ItemBindController::class)->middleware(['can:bg.item-bind.edit']);
    Route::resource('BoardGameInventory', BoardGameInventoryController::class)->middleware(['can:bg.player-inventory.edit']);

    Route::name('timer')->middleware(['can:bg.timer.edit'])->group(function() {
        Route::prefix('timer/')->controller(TimerController::class)->group(function() {
            Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
            Route::post('{timer}/force-delete', 'forceDelete')->name('force-delete');
            Route::post('{timer}/recovery', 'recovery')->name('recovery');
            Route::get('filters', 'getListFilters')->name('filters');
        });
        Route::resource('timer', TimerController::class);
    });

    Route::name('bgPlayerTimer')->middleware(['can:bg.player-timer.edit'])->group(function() {
        Route::prefix('bgPlayerTimer/')->controller(BgPlayerTimerController::class)->group(function() {
            Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
            Route::post('{bgPlayerTimer}/force-delete', 'forceDelete')->name('force-delete');
            Route::post('{bgPlayerTimer}/recovery', 'recovery')->name('recovery');
            Route::get('filters', 'getListFilters')->name('filters');
        });
        Route::resource('bgPlayerTimer', BgPlayerTimerController::class);
    });
});
