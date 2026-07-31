<?php

use App\Http\Controllers\BoardGame\BgAddGameController;
use Illuminate\Support\Facades\Route;

Route::prefix('add-game/')->controller(BgAddGameController::class)->group(function() {
    Route::get('check/{slug}/', 'check')->name('check');
    Route::post('save/{slug}/', 'save')->name('save');
});
