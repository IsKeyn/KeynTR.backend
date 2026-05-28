<?php

use App\Http\Controllers\Admin\BoardGame\ItemController;
use Illuminate\Support\Facades\Route;

Route::prefix('item/')->controller(ItemController::class)->group(function() {
    Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
    Route::post('{item}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{item}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
});
Route::resource('item', ItemController::class);
