<?php

use App\Http\Controllers\Admin\BoardGame\BgPlayerInteractionController;
use Illuminate\Support\Facades\Route;

Route::prefix('PlayerInteraction/')->controller(BgPlayerInteractionController::class)->group(function() {
    Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
    Route::post('{item}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{item}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
});
Route::resource('PlayerInteraction', BgPlayerInteractionController::class)
    ->parameters(['PlayerInteraction' => 'playerInteraction']);
