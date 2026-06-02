<?php

use App\Http\Controllers\Admin\BoardGame\BgPlayerTimerController;
use Illuminate\Support\Facades\Route;

Route::prefix('bgPlayerTimer/')->controller(BgPlayerTimerController::class)->group(function() {
    Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
    Route::post('{bgPlayerTimer}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{bgPlayerTimer}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
});
Route::resource('bgPlayerTimer', BgPlayerTimerController::class);
