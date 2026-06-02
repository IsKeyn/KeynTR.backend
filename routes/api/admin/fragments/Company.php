<?php

use App\Http\Controllers\Admin\AdminCompanyController;
use Illuminate\Support\Facades\Route;

Route::prefix('company/')->controller(AdminCompanyController::class)->group(function() {
    Route::post('{company}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{company}/recovery', 'recovery')->name('recovery');
});
Route::resource('company', AdminCompanyController::class);
