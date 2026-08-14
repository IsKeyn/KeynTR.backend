<?php

use App\Http\Controllers\BoardGame\BoardGameController;
use App\Http\Controllers\BoardGame\InteractionsController;
use App\Http\Controllers\BoardGame\LogController;
use App\Http\Controllers\BoardGame\PlayerGameController;
use App\Http\Controllers\BoardGame\PlayerInteractionController;
use App\Http\Controllers\BoardGame\StatsController;
use Illuminate\Support\Facades\Route;

Route::prefix('board-game/v2/')->name('v2.')->group(function() {
    Route::name('game.')->group(base_path('routes/api/board-game/fragments/Game.php'));
    Route::name('player.')->group(base_path('routes/api/board-game/fragments/Player.php'));
    Route::name('inventory.')->group(base_path('routes/api/board-game/fragments/Inventory.php'));
    Route::name('item.')->group(base_path('routes/api/board-game/fragments/Item.php'));
    Route::name('shop.')->group(base_path('routes/api/board-game/fragments/Shop.php'));
    Route::name('status-effect.')->group(base_path('routes/api/board-game/fragments/StatusEffect.php'));
    Route::name('board-status-effect.')->group(base_path('routes/api/board-game/fragments/BoardStatusEffect.php'));
    Route::name('board.')->group(base_path('routes/api/board-game/fragments/Board.php'));
    Route::name('dice.')->group(base_path('routes/api/board-game/fragments/Dice.php'));
    Route::name('timer.')->group(base_path('routes/api/board-game/fragments/Timer.php'));
    Route::name('add-game.')->group(base_path('routes/api/board-game/fragments/AddGame.php'));
    Route::name('debug.')->middleware(['auth:sanctum', 'can:bg.debug.edit'])->group(base_path('routes/api/board-game/fragments/Debug.php'));

    Route::controller(BoardGameController::class)->group(function() {
        Route::get('get/{slug}', 'getBySlug')->name('get-by-slug');
        Route::get('layout/get', 'getLayoutData')->name('get-layout-data');
        Route::get('list', 'getList')->name('list');
    });

    Route::prefix('interactions/')->controller(InteractionsController::class)->name('interactions.')->group(function() {
        Route::post('action', 'action')->name('action');
    });

    Route::prefix('player-game/')->controller(PlayerGameController::class)->name('player-game.')->group(function () {
        Route::get('get-player-list/{slug}', 'getPlayerList')->name('get-player-list');
        Route::get('get-player-available-list/{slug}', 'getAvailablePlayerGameList')->name('get-player-available-list');
        Route::get('get-spend-time', 'getSpendTime')->name('get-spend-time');
        Route::post('roll/{slug}', 'roll')->name('roll');
        Route::post('add', 'add')->name('add');
        Route::post('update', 'update')->name('update');
        Route::post('invite-to-coop', 'inviteToCoop')->name('invite-to-coop');
    });

    Route::prefix('player-interaction/')->controller(PlayerInteractionController::class)->name('player-interaction.')->group(function () {
        Route::get('player-interaction/{slug}/{name}', 'get')->name('get');
    });

    Route::prefix('log/')->controller(LogController::class)->name('log.')->group(function() {
        Route::get('get/{slug}/{name}', 'getPlayerLog')->name('getPlayerLog');
        Route::get('list/{slug}', 'getList')->name('getList');
    });

    Route::prefix('stats/')->controller(StatsController::class)->name('stats.')->group(function() {
        Route::get('get', 'get')->name('get');
    });
});
