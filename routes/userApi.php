<?php
use App\Http\Controllers\Auth\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::name('api.')->group(function() {
    Route::name('auth.')->prefix('auth/')->middleware('auth:sanctum')->group(function() {
        Route::get('user', [UserController::class, 'authUser'])->name('user');
    });
});
