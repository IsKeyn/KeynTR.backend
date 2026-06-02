<?php

use Illuminate\Support\Facades\Route;

Route::prefix('auth/')->group(function() {
    Route::name('guest.')->middleware('guest:api')->group(base_path('routes/api/auth/fragments/Guest.php'));
    Route::name('auth.')->middleware('auth:sanctum')->group(base_path('routes/api/auth/fragments/Auth.php'));
    Route::name('auth-and-guest.')->group(base_path('routes/api/auth/fragments/AuthAndGuest.php'));
});
