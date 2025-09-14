<?php

use App\Http\Controllers\MenuController;
use Illuminate\Support\Facades\Route;

Route::prefix('menu/')->controller(MenuController::class)->group(function() {
    Route::get('get', 'getMenuElements')->name('get-menu-elements');
    Route::get('getArticlesMenu', 'getArticlesMenu')->name('get-articles-menu');
    Route::get('getByCode', 'getByCode')->name('get-by-code');
});
