<?php
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
        Route::post('forgot-password', 'sendResetLink')->name('send-reset-link');
        Route::post('reset-password', 'resetPassword')->name('reset-password');
    });
});

// Действия авторизированного пользователя
Route::prefix('auth/')->middleware('auth:sanctum')->group(function() {
    Route::get('logout', [LoginController::class, 'logout'])->name('logout');

    Route::controller(UserController::class)->group(function() {
        Route::get('user', 'authUser')->name('user');
        Route::post('verification-notification', 'sendVerificationNotification')
            ->middleware(['throttle:6,1'])->name('verification.send');
        Route::post('setAvatar', 'setAvatar')->name('set-avatar');
        Route::post('update-profile', 'updateProfile')->name('update-profile');
        Route::put('set-settings', 'setSettings')->name('set-settings');
    });

    Route::controller(NotificationController::class)->prefix('notification/')->name('notification')->group(function () {
        Route::get('get', 'GetCurrentUserNotifications')->name('GetCurrentUserNotifications');
        Route::get('getCount', 'GetCountUserNotifications')->name('GetCountUserNotifications');
        Route::post('set', 'set')->name('set');
        Route::post('set-viewed', 'SetViewed')->name('set-viewed');
    });
});

// Общие действия как для авторизованного, так и для не авторизованного пользователя
Route::prefix('auth/')->controller(UserController::class)->group(function() {
    Route::get('getFullProfile/{name}', 'getFullProfile')->name('getFullProfile');
});

//    Route::get('user', [UserController::class, 'authUser'])->name('user'); // TODO где используется данный роут?
