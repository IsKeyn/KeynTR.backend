<?php

use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('user/')->group(function() {
    Route::get('list', [UserController::class, 'list'])->name('list');
});
