<?php

use App\Http\Controllers\Admin\BoardGame\ItemBindController;
use Illuminate\Support\Facades\Route;

Route::prefix('itemBind/')->controller(ItemBindController::class)->group(function() {
    Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
    Route::post('{itemBind}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{itemBind}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
});
Route::resource('itemBind', ItemBindController::class);
