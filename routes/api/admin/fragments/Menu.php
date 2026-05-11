<?php
use App\Http\Controllers\Admin\Menu\MenuController;
use Illuminate\Support\Facades\Route;

Route::prefix('menu/')->controller(MenuController::class)->group(function() {
    Route::post('{menu}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{menu}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
    Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
});
Route::resource('menu', MenuController::class);
