<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoardGame\PlayerStatusEffectController;

Route::prefix('status-effect/')
    ->controller(PlayerStatusEffectController::class)
    ->middleware('auth:sanctum')
    ->group(function() {
        Route::post('use', 'use')->name('use');
});
