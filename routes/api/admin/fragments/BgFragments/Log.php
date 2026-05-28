<?php

use App\Http\Controllers\Admin\BoardGame\BgLogController;
use Illuminate\Support\Facades\Route;

Route::prefix('Log/')->controller(BgLogController::class)->group(function() {
    Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
    Route::post('{Log}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{Log}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
});
Route::resource('Log', BgLogController::class);
