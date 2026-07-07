<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoardGame\BoardGameInventoryController;

Route::prefix('inventory/')
    ->controller(BoardGameInventoryController::class)
    ->middleware('auth:sanctum')
    ->group(function() {
        Route::post('useItem', 'useItem')->name('use-item');
        Route::post('sellItem', 'sellItem')->name('sell-item');
});
