<?php

use App\Http\Controllers\SiteGame\SaveStateController;
use Illuminate\Support\Facades\Route;

Route::prefix('save-state')->controller(SaveStateController::class)->group(function() {
    Route::get('get', 'get')->name('get')->middleware('auth:sanctum');
    Route::get('get-by-bg-player', 'getByBgPlayer')->name('getByBgPlayer')->middleware('auth:sanctum');
});
