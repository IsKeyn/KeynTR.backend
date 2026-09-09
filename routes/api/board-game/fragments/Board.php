<?php

use App\Http\Controllers\BoardGame\BoardCellController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoardGame\BoardController;

Route::prefix('board/')->controller(BoardController::class)->group(function() {
    Route::get('get/{slug}/', 'get')->name('get');
});

Route::prefix('board-cell/')->controller(BoardCellController::class)->group(function() {
    Route::middleware(['bg.check.is', 'bg.check.is_open', 'bg.check.active_player'])->group(function() {
        Route::get('get-current-player-review/', 'getCurrentPlayerReview')->name('get-current-player-review');
        Route::put('set-review/', 'setReview')->name('set-review');
    });

    Route::middleware(['bg.check.is', 'auth:sanctum'])->group(function() {
        Route::get('get-current-event-review/{slug}/{id}/', 'getCurrentEventReview')->name('get-current-event-review');
        Route::get('get-other-event-review/{slug}/{id}/', 'getOtherEventReview')->name('get-other-event-review');
    });
});
