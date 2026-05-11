<?php

use App\Http\Controllers\Admin\User\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('user/')->controller(UserController::class)->group(function() {
    Route::get('full-logout/{userId}', 'fullLogout')->name('full-logout');
    Route::post('{user}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{user}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
    Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
});
Route::resource('user', UserController::class);
