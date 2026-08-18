<?php

use App\Http\Controllers\User\GeoController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('user/')->group(function() {
    Route::get('list', [UserController::class, 'list'])->name('list');
    Route::get('get-by-ids', [UserController::class, 'getById'])->name('get-by-ids');
    Route::get('country', [GeoController::class, 'getCountry'])->name('country');
});
