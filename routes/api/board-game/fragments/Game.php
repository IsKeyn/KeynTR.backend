<?php

use App\Http\Controllers\BoardGame\BgGameController;
use Illuminate\Support\Facades\Route;

Route::prefix('game/')
    ->controller(BgGameController::class)
    ->group(function() {
        Route::get('get/{slug}/{gameslug}', 'get')->name('get');
        Route::get('getActionsWithGameInEventByGameSlug/{slug}/{gameslug}', 'getActionsWithGameInEventByGameSlug')
            ->name('getActionsWithGameInEventByGameSlug');
        Route::get('getActionsWithGameInOtherEventsByGameSlug/{slug}/{gameslug}', 'getActionsWithGameInOtherEventsByGameSlug')
            ->name('getActionsWithGameInOtherEventsByGameSlug');
});
