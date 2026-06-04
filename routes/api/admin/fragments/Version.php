<?php

use App\Http\Controllers\Admin\AdminVersionController;
use Illuminate\Support\Facades\Route;

Route::prefix('version/')->controller(AdminVersionController::class)->group(function() {
    Route::get('getByEntity', 'getByEntity')->name('get-by-entity');
});
Route::resource('version', AdminVersionController::class);
