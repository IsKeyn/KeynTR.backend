<?php

use App\Http\Controllers\Admin\AdminMediaPagesController;
use Illuminate\Support\Facades\Route;

Route::prefix('media/')->controller(AdminMediaPagesController::class)->group(function() {
    Route::post('multi-store', 'multiStore')->name('multi-store');
});
Route::resource('media', AdminMediaPagesController::class);
