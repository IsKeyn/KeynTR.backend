<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoardGame\BoardGameController;
use App\Http\Controllers\BoardGame\BoardGameInventoryController;
use App\Http\Controllers\BoardGame\BoardGamePlayerController;
use App\Http\Controllers\BoardGame\DiceController;
//use App\Http\Controllers\BoardGame\GameListController;
use App\Http\Controllers\BoardGame\LogController;
use App\Http\Controllers\BoardGame\PlayerGameController;
use App\Http\Controllers\BoardGame\PositionController;
use App\Http\Controllers\Admin\BoardGame\ItemBindController;
use App\Http\Controllers\BoardGame\StatsController;
use App\Http\Controllers\BoardGame\TimerController;

/* TODO для сущностей, которые требует авторизации добавить middleware('auth:sanctum') */
Route::prefix('board-game/')->name('v1.')->group(function() {
    Route::get('get/{slug}', [BoardGameController::class, 'getBySlug'])->name('get-by-slug');
    Route::get('get-list', [BoardGameController::class, 'getList'])->name('get-list');

    Route::get('getBoardInfo', [BoardGameController::class, 'getBoardInfo'])->name('get-board-info');
    Route::get('getItemAndInventory', [BoardGameController::class, 'getItemAndInventory'])->name('get-item-and-inventory');
    Route::get('getStreamersOnline', [BoardGameController::class, 'getStreamersOnline'])->name('streamers-online');
    Route::post('roll-dice', [DiceController::class, 'rollDice'])->name('roll-dice');

    Route::prefix('player/')->controller(BoardGamePlayerController::class)->name('player.')->group(function() {
        Route::get('get/{id}', 'get')->name('get');
        Route::get('list', 'list')->name('list');
        Route::post('add', 'add')->name('add');
        Route::post('updatedPoints', 'updatedPoints')->name('update-points');
    });

    Route::prefix('log/')->controller(LogController::class)->name('log.')->group(function() {
        Route::post('add', 'add')->name('add');
        Route::get('list', 'getLogListById')->name('list');
    });

    Route::prefix('stats/')->controller(StatsController::class)->name('stats.')->group(function() {
        Route::get('get', 'get')->name('get');
    });

    Route::prefix('position/')->controller(PositionController::class)->name('position.')->group(function() {
        Route::post('add', 'add')->name('add');
    });

    Route::prefix('items/')->controller(ItemBindController::class)->name('items.')->group(function() {
        Route::get('list', 'list')->name('list');
    });

    Route::prefix('inventory/')->controller(BoardGameInventoryController::class)->name('inventory.')->group(function() {
        Route::post('add', 'add')->name('add');
        Route::post('list', 'list')->name('list');
        Route::delete('destroy', 'destroy')->name('destroy');
        Route::post('use', 'useItem')->name('use-item');
    });

//    Route::prefix('game-list/')->controller(GameListController::class)->name('game-list.')->group(function() {
//        Route::get('list', 'list')->name('list');
//    });

    Route::prefix('player-game/')->controller(PlayerGameController::class)->name('player-game.')->group(function () {
        Route::get('get-player-list', 'getPlayerList')->name('get-player-list');
        Route::get('get-spend-time', 'getSpendTime')->name('get-spend-time');
        Route::post('roll', 'roll')->name('roll');
        Route::post('add', 'add')->name('add');
        Route::post('update', 'update')->name('update');
    });

    Route::prefix('timer/')->controller(TimerController::class)->name('timer.')->group(function () {
        Route::post('start', 'start')->name('start');
        Route::post('stop', 'stop')->name('stop');
        Route::post('edit', 'edit')->name('edit');
        Route::post('status', 'status')->name('status');
        Route::get('list', 'list')->name('list');
        Route::post('add', 'add')->name('add');
        Route::delete('delete', 'delete')->name('delete');
    });
});
