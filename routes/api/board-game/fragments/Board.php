<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoardGame\BoardController;

Route::prefix('board/')->controller(BoardController::class)->group(function() {
    Route::get('get/{slug}/', 'get')->name('get');
});
