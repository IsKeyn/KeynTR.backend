<?php

use App\Http\Controllers\User\MagicLinkController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UserController;

/* Общие действия как для авторизованного, так и для не авторизованного пользователя */
Route::controller(UserController::class)->group(function() {
    Route::get('getFullProfile/{name}', 'getFullProfile')->name('getFullProfile');
    Route::prefix('magical-link/')->controller(MagicLinkController::class)->name('magic-link.')->group(function() {
        Route::get('/login/{token}','login')->name('login');
    });
});
