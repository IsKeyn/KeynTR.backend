<?php

use App\Http\Controllers\Admin\BoardGame\TimerController;
use Illuminate\Support\Facades\Route;

Route::prefix('timer/')->controller(TimerController::class)->group(function() {
    Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
    Route::post('{timer}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{timer}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
});
Route::resource('timer', TimerController::class);
