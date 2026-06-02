<?php

use App\Http\Controllers\Admin\AdminMovieController;
use Illuminate\Support\Facades\Route;

Route::prefix('movie/')->controller(AdminMovieController::class)->group(function() {
    Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
    Route::post('{game}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{game}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
});
Route::resource('movie', AdminMovieController::class);
