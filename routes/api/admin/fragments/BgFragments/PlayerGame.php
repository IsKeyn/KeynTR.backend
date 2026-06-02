<?php

use App\Http\Controllers\Admin\BoardGame\BgPlayerGameController;
use Illuminate\Support\Facades\Route;

Route::prefix('PlayerGame/')->controller(BgPlayerGameController::class)->group(function() {
    Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
    Route::post('{playerGame}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{playerGame}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
});
Route::resource('PlayerGame', BgPlayerGameController::class)
    ->parameters(['PlayerGame' => 'playerGame']);
