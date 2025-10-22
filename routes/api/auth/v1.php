<?php

use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\User\AllNotifications;
use App\Http\Controllers\User\MagicLinkController;
use App\Http\Controllers\User\MessageController;
use App\Services\User\UserService;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\User\NotificationController;

// Авторизация и стандартные действия не авторизированного пользователя
Route::prefix('auth/')->middleware('guest:api')->group(function() {
    Route::post('login', [LoginController::class, 'login'])->name('login');

    Route::controller(RegisterController::class)->group(function() {
        Route::post('register', 'register')->name('register');

    });

    Route::controller(ResetPasswordController::class)->group(function() {
        Route::post('forgot-password', 'sendResetLink')->name('sendResetLink');
        Route::post('reset-password', 'resetPassword')->name('resetPassword');
    });
});

// Подтверждение email
Route::get('/auth/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    return UserService::verifyEmail($request);
})->middleware(['auth:sanctum', 'signed'])->name('verification.verify');

// Действия авторизированного пользователя
Route::prefix('auth/')->middleware('auth:sanctum')->group(function() {
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
    });

    Route::controller(NotificationController::class)->prefix('notification/')->name('notification')->group(function () {
        Route::get('get', 'getCurrentUserNotifications')->name('getCurrentUserNotifications');
        Route::get('getCount', 'getCountUserNotifications')->name('getCountUserNotifications');
        Route::post('set', 'set')->name('set');
        Route::post('set-viewed', 'setViewed')->name('setViewed');
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

});

// Общие действия как для авторизованного, так и для не авторизованного пользователя
Route::prefix('auth/')->controller(UserController::class)->group(function() {
    Route::get('getFullProfile/{name}', 'getFullProfile')->name('getFullProfile');
    Route::prefix('magical-link/')->controller(MagicLinkController::class)->name('magic-link.')->group(function() {
        Route::get('/login/{token}','login')->name('login');
    });
});

//    Route::get('user', [UserController::class, 'authUser'])->name('user'); // TODO где используется данный роут?
