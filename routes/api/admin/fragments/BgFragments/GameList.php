<?php

use App\Http\Controllers\Admin\BoardGame\BgGameListController;
use Illuminate\Support\Facades\Route;

Route::prefix('GameList/')->controller(BgGameListController::class)->group(function() {
    Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
    Route::post('{gameList}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{gameList}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
});
Route::resource('GameList', BgGameListController::class)
    ->parameters(['GameList' => 'gameList']);
