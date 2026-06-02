<?php

use App\Http\Controllers\Admin\BoardGame\BoardGameController;
use Illuminate\Support\Facades\Route;

Route::prefix('BoardGame/')->controller(BoardGameController::class)->group(function() {
    Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
    Route::post('{boardGame}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{boardGame}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
});
Route::resource('BoardGame', BoardGameController::class);
