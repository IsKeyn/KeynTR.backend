<?php

use App\Http\Controllers\Admin\BoardGame\BgPlayerStatusEffectController;
use Illuminate\Support\Facades\Route;

Route::prefix('PlayerStatusEffect/')->controller(BgPlayerStatusEffectController::class)->group(function() {
    Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
    Route::post('{playerStatusEffect}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{playerStatusEffect}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
});
Route::resource('PlayerStatusEffect', BgPlayerStatusEffectController::class)
    ->parameters(['PlayerStatusEffect' => 'playerStatusEffect']);
