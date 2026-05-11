<?php

use App\Http\Controllers\Admin\User\PermissionController;
use Illuminate\Support\Facades\Route;

Route::prefix('permission/')->controller(PermissionController::class)->group(function() {
    Route::post('{permission}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{permission}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
});
Route::resource('permission', PermissionController::class);
