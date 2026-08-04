<?php

use App\Http\Controllers\Admin\BoardGame\BgAddGameController;
use Illuminate\Support\Facades\Route;

Route::prefix('AddGame/')->controller(BgAddGameController::class)->group(function() {
    Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
    Route::post('{addGame}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{addGame}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
});
Route::resource('AddGame', BgAddGameController::class)
    ->parameters(['AddGame' => 'addGame']);
