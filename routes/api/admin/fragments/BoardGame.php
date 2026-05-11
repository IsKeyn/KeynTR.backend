<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BoardGame\ItemBindController;
use App\Http\Controllers\Admin\BoardGame\ItemController;
use App\Http\Controllers\Admin\BoardGame\BoardGameController;
use App\Http\Controllers\BoardGame\BoardGameInventoryController;

Route::prefix('BoardGame')->group(function () {
    Route::resource('BoardGame', BoardGameController::class)->middleware(['can:bg.edit']);
    Route::resource('Item', ItemController::class)->middleware(['can:bg.item.edit']);
    Route::resource('ItemBind', ItemBindController::class)->middleware(['can:bg.item-bind.edit']);
    Route::resource('BoardGameInventory', BoardGameInventoryController::class)->middleware(['can:bg.player-inventory.edit']);
});
