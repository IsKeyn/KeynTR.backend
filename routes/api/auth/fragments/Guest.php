<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OauthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Support\Facades\Route;

/* Авторизация и стандартные действия не авторизированного пользователя */
Route::controller(RegisterController::class)->group(function() {
    Route::post('register', 'register')->name('register');
});

Route::post('login', [LoginController::class, 'login'])->name('login');

Route::controller(ResetPasswordController::class)->group(function() {
    Route::post('forgot-password', 'sendResetLink')->name('sendResetLink');
    Route::post('reset-password', 'resetPassword')->name('resetPassword');
});

Route::controller(OauthController::class)->prefix('{oauthName}/')->name('{oauthName}')->group(function() {
    Route::get('redirect', [OauthController::class, 'redirect'])->name('redirect');
    Route::post('apiCallback', [OauthController::class, 'apiCallback'])->name('apiCallback');
});
