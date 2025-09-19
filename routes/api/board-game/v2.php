<?php

use App\Http\Controllers\BoardGame\BoardGamePlayerController;
use App\Http\Controllers\BoardGame\ItemController;
use App\Http\Controllers\BoardGame\LogController;
use App\Http\Controllers\BoardGame\PlayerGameController;
use Illuminate\Support\Facades\Route;

Route::prefix('board-game/v2/')->name('v2.')->group(function() {
    Route::prefix('player/')->controller(BoardGamePlayerController::class)->name('player.')->group(function() {
        Route::get('get/{slug}/{name}', 'getPlayer')->name('getPlayer');
        Route::get('current/{slug}', 'getCurrent')->middleware('auth:sanctum')->name('getCurrent');
        Route::get('list/{slug}', 'getList')->name('getList');
        Route::get('listWithInventory/{slug}', 'getListWithInventory')->name('getListWithInventory');

        Route::get('getInventory/{slug}/{name}', 'getInventory')->name('getInventory');
        Route::get('getGames/{slug}/{name}', 'getGames')->name('getGames');
        Route::get('getCurrentGame/{slug}/{name}', 'getCurrentGame')->name('getCurrentGame');

        Route::get('item/gamblingGame/{slug}', 'getDataForItemGamblingGame')->name('getDataForItemGamblingGame');
        Route::post('rollItem/{slug}', 'rollItem')->name('rollItem');
    });

    Route::prefix('player-game/')->controller(PlayerGameController::class)->name('player-game.')->group(function () {
        Route::get('get-player-list/{slug}', 'getPlayerList')->name('get-player-list');
//        Route::get('get-spend-time', 'getSpendTime')->name('get-spend-time');
//        Route::post('roll', 'roll')->name('roll');
//        Route::post('add', 'add')->name('add');
//        Route::post('update', 'update')->name('update');
    });

    Route::prefix('item/')->controller(ItemController::class)->name('item.')->group(function() {
        Route::get('list', 'list')->name('list');
        Route::get('list/{slug}', 'getList')->name('getList');
    });

    Route::prefix('log/')->controller(LogController::class)->name('log.')->group(function() {
        Route::get('get/{slug}/{name}', 'getPlayerLog')->name('getPlayerLog');
        Route::get('list/{slug}', 'getList')->name('getList');
    });
});
