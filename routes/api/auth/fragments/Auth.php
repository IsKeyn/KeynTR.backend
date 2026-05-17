<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\User\NotificationController;
use App\Http\Controllers\User\MessageController;
use App\Http\Controllers\User\AllNotifications;

/* Действия авторизированного пользователя */
Route::get('logout', [LoginController::class, 'logout'])->name('logout');

Route::controller(UserController::class)->group(function() {
    Route::get('user', 'authUser')->name('user');
    Route::post('verification-notification', 'sendVerificationNotification')
        ->middleware(['throttle:6,1'])->name('verification.send');
    Route::post('setAvatar', 'setAvatar')->name('setAvatar');
    Route::post('update-profile', 'updateProfile')->name('updateProfile');
    Route::put('set-settings', 'setSettings')->name('setSettings');
    Route::post('change-password', 'changePassword')->name('changePassword');
    Route::post('generate-auth-link', 'generateAuthLink')->name('generate-auth-link');

    Route::post('get-sanctum-token', 'getSanctumToken')->name('get-sanctum-token');
});

Route::controller(NotificationController::class)->prefix('notification/')->name('notification')->group(function () {
    Route::get('get', 'getCurrentUserNotifications')->name('getCurrentUserNotifications');
    Route::get('getCount', 'getCountUserNotifications')->name('getCountUserNotifications');
    Route::post('set', 'set')->name('set');
    Route::post('set-viewed', 'setViewed')->name('setViewed');
    Route::post('set-viewed-all', 'setViewedAll')->name('setViewedAll');
});

Route::controller(MessageController::class)->prefix('message/')->name('message')->group(function () {
    Route::get('get', 'getCurrentUserMessages')->name('getCurrentUserMessages');
//        Route::get('getCount', 'GetCountUserNotifications')->name('GetCountUserNotifications');
//        Route::post('set', 'set')->name('set');
//        Route::post('set-viewed', 'SetViewed')->name('set-viewed');
});

Route::controller(AllNotifications::class)->prefix('allNotifications/')->name('allNotifications')->group(function () {
    Route::get('get', 'get')->name('getNotifications');
});
