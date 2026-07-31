<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoardGame\BoardController;

Route::prefix('boardStatusEffect/')
    ->controller(BoardController::class)
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::post('use', 'usePositionEffect')->name('use');
});
