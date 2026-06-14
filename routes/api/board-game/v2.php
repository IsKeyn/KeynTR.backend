<?php

use App\Http\Controllers\BoardGame\BoardController;
use App\Http\Controllers\BoardGame\BoardGameController;
use App\Http\Controllers\BoardGame\BoardGameInventoryController;
use App\Http\Controllers\BoardGame\BoardGamePlayerController;
use App\Http\Controllers\BoardGame\DiceController;
use App\Http\Controllers\BoardGame\GameListController;
use App\Http\Controllers\BoardGame\InteractionsController;
use App\Http\Controllers\BoardGame\ItemController;
use App\Http\Controllers\BoardGame\LogController;
use App\Http\Controllers\BoardGame\PlayerGameController;
use App\Http\Controllers\BoardGame\PlayerInteractionController;
use App\Http\Controllers\BoardGame\PlayerStatusEffectController;
use App\Http\Controllers\BoardGame\StatsController;
use Illuminate\Support\Facades\Route;

Route::prefix('board-game/v2/')->name('v2.')->group(function() {
    Route::name('player')->group(base_path('routes/api/board-game/fragments/Player.php'));
    Route::name('timer')->group(base_path('routes/api/board-game/fragments/Timer.php'));

    Route::post('roll-dice', [DiceController::class, 'rollDice'])->name('roll-dice');

    Route::controller(BoardGameController::class)->group(function() {
        Route::get('get/{slug}', 'getBySlug')->name('get-by-slug');
        Route::get('layout/get', 'getLayoutData')->name('get-layout-data');
        Route::get('list', 'getList')->name('list');
    });

    Route::prefix('board/')->controller(BoardController::class)->name('board.')->group(function() {
        Route::get('get/{slug}/', 'get')->name('get');
    });

    Route::prefix('inventory/')->controller(BoardGameInventoryController::class)->name('inventory.')->group(function() {
        Route::post('use', 'useItem')->name('use-item');
    });

    Route::prefix('game-list/')->controller(GameListController::class)->name('game-list.')->group(function() {
        Route::get('list', 'list')->name('list');
    });

    Route::prefix('status-effect/')->controller(PlayerStatusEffectController::class)->name('status-effect.')->group(function() {
        Route::post('use', 'use')->name('use');
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

    Route::prefix('boardStatusEffect/')->controller(BoardController::class)->name('board-status-effect.')->group(function () {
        Route::post('use', 'usePositionEffect')->name('use');
    });

    Route::prefix('item/')->controller(ItemController::class)->name('item.')->group(function() {
        Route::get('list', 'list')->name('list');
        Route::get('list/{slug}', 'getList')->name('getList');
    });

    Route::prefix('log/')->controller(LogController::class)->name('log.')->group(function() {
        Route::get('get/{slug}/{name}', 'getPlayerLog')->name('getPlayerLog');
        Route::get('list/{slug}', 'getList')->name('getList');
    });

    Route::prefix('stats/')->controller(StatsController::class)->name('stats.')->group(function() {
        Route::get('get', 'get')->name('get');
    });
});
