<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
 * Подтверждение регистрации
 */
Route::get('/auth/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    $user = $request->user();

    return $user;
})->middleware(['auth:sanctum', 'signed'])->name('verification.verify');

Route::name('api.')->group(function() {
    Route::name('auth.')->prefix('auth/')->middleware('auth:sanctum')->group(function() {
        Route::get('user', [UserController::class, 'authUser'])->name('user');
        Route::get('logout', [LoginController::class, 'logout'])->name('logout');
        Route::post('verification-notification', [UserController::class, 'sendVerificationNotification'])->middleware(['throttle:6,1'])->name('verification.send');;
    });
});
