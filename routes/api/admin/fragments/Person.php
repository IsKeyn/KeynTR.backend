<?php

use App\Http\Controllers\Admin\AdminPersonController;
use Illuminate\Support\Facades\Route;

Route::prefix('person/')->controller(AdminPersonController::class)->group(function() {
    Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
    Route::post('{person}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{person}/recovery', 'recovery')->name('recovery');
});
Route::resource('person', AdminPersonController::class);
