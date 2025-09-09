<?php

use App\Http\Controllers\BoardGame\BoardGamePlayerController;
use Illuminate\Support\Facades\Route;

Route::prefix('board-game/v2/')->name('v2.')->group(function() {
    Route::prefix('player/')->controller(BoardGamePlayerController::class)->name('player.')->group(function() {
        Route::get('get/{slug}/{name}', 'getByBoardGameSlugAndUserName')->name('getByBoardGameSlugAndUserName');
        Route::get('current/{slug}', 'getByBoardGameSlug')->middleware('auth:sanctum')->name('getByBoardGameSlug');
        Route::get('list/{slug}', 'listByBoardGameSlug')->name('listByBoardGameSlug');

        Route::get('getInventory/{slug}/{name}', 'getInventoryByBoardGameSlugAndUserName')->name('getInventoryByBoardGameSlugAndUserName');
        Route::get('getGames/{slug}/{name}', 'getGamesByBoardGameSlugAndUserName')->name('getGamesByBoardGameSlugAndUserName');
    });
});
