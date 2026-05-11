<?php

use App\Http\Controllers\Admin\Games\GamesApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/')->group(function() {
    Route::prefix('games/')
        ->name('games.')
        ->controller(GamesApiController::class)
        ->middleware(['can:game.edit'])
        ->group(function() {
        Route::get('search', 'search')->name('search');
        Route::post('check', 'check')->name('check');
        Route::post('add', 'add')->name('add');
    });
});
