<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\TwitchController;
use App\Http\Controllers\Auth\VkAuthController;
use App\Http\Controllers\Auth\YandexAuthController;
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

Route::controller(TwitchController::class)->prefix('twitch/')->name('twitch')->group(function() {
    Route::get('redirect', [TwitchController::class, 'redirect'])->name('redirect');
    Route::post('apiCallback', [TwitchController::class, 'apiCallback'])->name('apiCallback');
});

Route::controller(YandexAuthController::class)->prefix('yandex/')->name('yandex')->group(function() {
    Route::get('redirect', [YandexAuthController::class, 'redirect'])->name('redirect');
    Route::post('apiCallback', [YandexAuthController::class, 'apiCallback'])->name('apiCallback');
});

Route::controller(VkAuthController::class)->prefix('vk/')->name('vk')->group(function() {
    Route::get('redirect', [VkAuthController::class, 'redirect'])->name('redirect');
    Route::post('apiCallback', [VkAuthController::class, 'apiCallback'])->name('apiCallback');
});
