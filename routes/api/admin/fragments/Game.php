<?php

use App\Http\Controllers\Admin\AdminGameController;
use Illuminate\Support\Facades\Route;

Route::prefix('game/')->controller(AdminGameController::class)->group(function() {
    Route::post('multi-store', 'multiStore')->name('multi-store');

    Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
    Route::post('{game}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{game}/recovery', 'recovery')->name('recovery');
});
Route::resource('game', AdminGameController::class);
