<?php

use App\Http\Controllers\BoardGame\ShopItemController;
use Illuminate\Support\Facades\Route;

Route::prefix('shop/')->controller(ShopItemController::class)->group(function() {
    Route::get('list/{slug}', 'getList')->name('getList');
    Route::post('buy', 'buy')->name('buy');
    Route::post('withdrawn', 'withdrawn')->name('withdrawn');
});
