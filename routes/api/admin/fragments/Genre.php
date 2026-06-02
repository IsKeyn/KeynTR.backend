<?php

use App\Http\Controllers\Admin\AdminGenreController;
use Illuminate\Support\Facades\Route;

Route::prefix('genre/')->controller(AdminGenreController::class)->group(function() {
    Route::post('{genre}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{genre}/recovery', 'recovery')->name('recovery');
});
Route::resource('genre', AdminGenreController::class);
