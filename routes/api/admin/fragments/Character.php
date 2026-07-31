<?php

use App\Http\Controllers\Admin\AdminCharacterController;
use Illuminate\Support\Facades\Route;

Route::prefix('character/')->controller(AdminCharacterController::class)->group(function() {
    Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
    Route::post('{character}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{character}/recovery', 'recovery')->name('recovery');
});
Route::resource('character', AdminCharacterController::class);
