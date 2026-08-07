<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoardGame\PlayerStatusEffectController;

Route::prefix('status-effect/')
    ->controller(PlayerStatusEffectController::class)
    ->group(function() {
        Route::get('list/{slug}', 'getList')->name('getList');
        Route::post('use', 'use')->name('use')->middleware('auth:sanctum');
});
