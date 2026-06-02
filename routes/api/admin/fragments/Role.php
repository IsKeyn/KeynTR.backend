<?php

use App\Http\Controllers\Admin\User\RoleController;
use Illuminate\Support\Facades\Route;

Route::prefix('role/')->controller(RoleController::class)->group(function() {
    Route::post('{role}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{role}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
    Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
});
Route::resource('role', RoleController::class);
