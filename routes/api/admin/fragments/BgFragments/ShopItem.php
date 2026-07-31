<?php

use App\Http\Controllers\Admin\BoardGame\BgShopItemController;
use Illuminate\Support\Facades\Route;

Route::prefix('ShopItem/')->controller(BgShopItemController::class)->group(function() {
    Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
    Route::post('{shopItem}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{shopItem}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
});
Route::resource('ShopItem', BgShopItemController::class)
    ->parameters(['ShopItem' => 'shopItem']);
