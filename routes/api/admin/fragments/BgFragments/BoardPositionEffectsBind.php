<?php

use App\Http\Controllers\Admin\BoardGame\BoardPositionEffectBindController;
use Illuminate\Support\Facades\Route;

Route::prefix('BoardPositionEffectsBind/')->controller(BoardPositionEffectBindController::class)->group(function() {
    Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
    Route::post('{BoardPositionEffectsBind}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{BoardPositionEffectsBind}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
});
Route::resource('BoardPositionEffectsBind', BoardPositionEffectBindController::class);
