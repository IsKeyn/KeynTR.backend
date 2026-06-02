<?php

use App\Http\Controllers\Admin\TagController;
use Illuminate\Support\Facades\Route;

Route::prefix('tag/')->controller(TagController::class)->group(function() {
    Route::post('{tag}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{tag}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
});
Route::resource('tag', TagController::class);
