<?php

use App\Http\Controllers\Admin\BoardGame\BoardController;
use Illuminate\Support\Facades\Route;

Route::prefix('Board/')->controller(BoardController::class)->group(function() {
    Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
    Route::post('{Board}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{Board}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
});
Route::resource('Board', BoardController::class);
