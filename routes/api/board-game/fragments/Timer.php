<?php

use App\Http\Controllers\BoardGame\TimerController;
use Illuminate\Support\Facades\Route;

Route::prefix('timer/')->controller(TimerController::class)->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('start', 'start')->name('start');
        Route::post('stop', 'stop')->name('stop');
        Route::post('edit', 'edit')->name('edit');
        Route::put('set-settings', 'setSettings')->name('set-settings');
        Route::get('list', 'list')->name('list');
        Route::post('add', 'add')->name('add');
        Route::delete('delete', 'delete')->name('delete');
    });

    Route::post('status', 'status')->name('status');
});
