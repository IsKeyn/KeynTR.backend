<?php

use App\Http\Controllers\Admin\GroupController;
use Illuminate\Support\Facades\Route;

Route::prefix('group/')->controller(GroupController::class)->group(function() {
    Route::post('{group}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{group}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
});
Route::resource('group', GroupController::class);
