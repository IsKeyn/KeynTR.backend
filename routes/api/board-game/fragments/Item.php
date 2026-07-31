<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoardGame\ItemController;

Route::prefix('item/')->controller(ItemController::class)->group(function() {
    Route::get('list', 'list')->name('list');
    Route::get('list/{slug}', 'getList')->name('getList');
});
