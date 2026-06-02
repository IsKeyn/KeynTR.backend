<?php

use App\Http\Controllers\Admin\BoardGame\InventoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('Inventory/')->controller(InventoryController::class)->group(function() {
    Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
    Route::post('{Inventory}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{Inventory}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
});
Route::resource('Inventory', InventoryController::class);
