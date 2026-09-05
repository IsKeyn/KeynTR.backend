<?php

use App\Http\Controllers\BoardGame\BoardCellController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoardGame\BoardController;

Route::prefix('board/')->controller(BoardController::class)->group(function() {
    Route::get('get/{slug}/', 'get')->name('get');
});

Route::prefix('board-cell/')->controller(BoardCellController::class)->group(function() {
    Route::get('get-current-user-review/', 'getCurrentUserReview')
        ->middleware(['bg.check.is', 'auth:sanctum'])
        ->name('get-current-user-review');
    Route::get('set-review/', 'setReview')
        ->middleware(['bg.check.is', 'bg.check.is_open', 'bg.check.active_player'])
        ->name('set-review');
});
