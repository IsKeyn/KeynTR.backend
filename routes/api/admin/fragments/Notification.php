<?php

use App\Http\Controllers\Admin\User\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('notification/')->controller(NotificationController::class)->group(function() {
    Route::post('{notification}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{notification}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
});
Route::resource('notification', NotificationController::class);
