<?php

use App\Http\Controllers\Admin\BoardGame\BgPlayerController;
use Illuminate\Support\Facades\Route;

Route::prefix('BoardGamePlayer/')->controller(BgPlayerController::class)->group(function() {
    Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
    Route::post('{BoardGamePlayer}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{BoardGamePlayer}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
});
Route::resource('BoardGamePlayer', BgPlayerController::class);
