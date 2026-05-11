<?php

use App\Http\Controllers\Admin\AdminSeriesController;
use Illuminate\Support\Facades\Route;

Route::prefix('series/')->controller(AdminSeriesController::class)->group(function() {
    Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
    Route::post('{series}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{series}/recovery', 'recovery')->name('recovery');
});
Route::resource('series', AdminSeriesController::class);
