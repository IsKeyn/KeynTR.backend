<?php

use App\Http\Controllers\Admin\AdminGamingPlatformController;
use Illuminate\Support\Facades\Route;

Route::prefix('gaming-platform/')->controller(AdminGamingPlatformController::class)->group(function() {
    Route::post('{gamingPlatform}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{gamingPlatform}/recovery', 'recovery')->name('recovery');
});
Route::resource('gaming-platform', AdminGamingPlatformController::class);
