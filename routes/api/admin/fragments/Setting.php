<?php

use App\Http\Controllers\Admin\SettingController;
use Illuminate\Support\Facades\Route;

Route::prefix('setting/')->controller(SettingController::class)->group(function() {
    Route::post('{setting}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{setting}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
});
Route::resource('setting', SettingController::class);
