<?php

use App\Http\Controllers\Admin\BoardGame\BoardPositionEffectController;
use Illuminate\Support\Facades\Route;

Route::prefix('BoardPositionEffect/')->controller(BoardPositionEffectController::class)->group(function() {
    Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
    Route::post('{BoardPositionEffect}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{BoardPositionEffect}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
});
Route::resource('BoardPositionEffect', BoardPositionEffectController::class);
