<?php

use App\Http\Controllers\BoardGame\BoardGamePlayerController;
use Illuminate\Support\Facades\Route;

Route::prefix('player/')->controller(BoardGamePlayerController::class)->group(function() {
    Route::post('add', 'add')->name('add');
    Route::get('get/{slug}/{name}', 'getPlayer')->name('getPlayer');
    Route::get('getPlayerWithInventory', 'getPlayerWithInventory')->name('getPlayerWithInventory');
    Route::get('current/{slug}', 'getCurrent')->middleware('auth:sanctum')->name('getCurrent');
    Route::get('list/{boardGame:slug}', 'getList')->name('getList');
    Route::get('filters', 'getListFilters')->name('filters');
    Route::get('listWithInventory/{slug}', 'getListWithInventory')->name('getListWithInventory');

    Route::get('getEvents/{slug}/{name}', 'getEvents')->name('getEvents');

    Route::get('getGames/{slug}/{name}', 'getGames')->name('getGames');
    Route::get('getCurrentGame/{slug}/{name}', 'getCurrentGame')->name('getCurrentGame');
    Route::get('getStatusEffects/{slug}/{name}', 'getStatusEffects')->name('getStatusEffects');
    Route::get('getInventory/{slug}/{name}', 'getInventory')->name('getInventory');
    Route::get('interactions/get/{slug}', 'getInteractions')->name('getInteractions');
    Route::get('item/gamblingGame/{slug}', 'getDataForItemGamblingGame')->name('getDataForItemGamblingGame');

    Route::middleware('auth:sanctum')->group(function() {
        Route::post('rollItem/{slug}', 'rollItem')->name('rollItem');
        Route::post('setSetting/{slug}', 'setSetting')->name('set-setting');
        Route::post('setPlayerSettings/{slug}', 'setPlayerSettings')
            ->middleware(['bg.check.is', 'bg.check.dont_close', 'bg.check.active_player'])
            ->name('set-player-setting');
        Route::post('setPlayerBackground/{slug}', 'setPlayerBackground')->name('set-player-background');
    });
});
