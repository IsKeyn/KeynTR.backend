<?php

use App\Http\Controllers\BoardGame\BoardGamePlayerController;
use Illuminate\Support\Facades\Route;

Route::prefix('board-game/v2/')->name('v2.')->group(function() {
    Route::prefix('player/')->controller(BoardGamePlayerController::class)->name('player.')->group(function() {
        Route::get('current/{slug}', 'getByBoardGameSlug')->middleware('auth:sanctum')->name('getByBoardGameSlug');
    });
});
