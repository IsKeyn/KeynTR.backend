<?php

use App\Http\Controllers\Admin\BoardGame\BgPlayerPositionController;
use Illuminate\Support\Facades\Route;

Route::prefix('PlayerPosition/')->controller(BgPlayerPositionController::class)->group(function() {
    Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
    Route::post('{PlayerPosition}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{PlayerPosition}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
});
Route::resource('PlayerPosition', BgPlayerPositionController::class);
