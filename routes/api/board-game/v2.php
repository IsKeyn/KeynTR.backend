<?php

use App\Http\Controllers\BoardGame\BoardGamePlayerController;
use App\Http\Controllers\BoardGame\ItemController;
use Illuminate\Support\Facades\Route;

Route::prefix('board-game/v2/')->name('v2.')->group(function() {
    Route::prefix('player/')->controller(BoardGamePlayerController::class)->name('player.')->group(function() {
        Route::get('get/{slug}/{name}', 'getPlayer')->name('getPlayer');
        Route::get('current/{slug}', 'getCurrent')->middleware('auth:sanctum')->name('getCurrent');
        Route::get('list/{slug}', 'getList')->name('getList');

        Route::get('getInventory/{slug}/{name}', 'getInventory')->name('getInventory');
        Route::get('getGames/{slug}/{name}', 'getGames')->name('getGames');
        Route::get('getCurrentGame/{slug}/{name}', 'getCurrentGame')->name('getCurrentGame');
    });

    Route::prefix('item/')->controller(ItemController::class)->name('item.')->group(function() {
        Route::get('list', 'list')->name('list');
        Route::get('list/{slug}', 'getList')->name('getList');
    });
});
