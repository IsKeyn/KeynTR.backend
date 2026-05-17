<?php

use App\Http\Controllers\Layout\LayoutController;
use Illuminate\Support\Facades\Route;

Route::prefix('layout')->controller(LayoutController::class)->group(function() {
    Route::get('get', 'getData')->name('get');
});
