<?php

use App\Http\Controllers\Admin\BoardGame\StatusEffectController;
use Illuminate\Support\Facades\Route;

Route::prefix('StatusEffect/')->controller(StatusEffectController::class)->group(function() {
    Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
    Route::post('{StatusEffect}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{StatusEffect}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
});
Route::resource('StatusEffect', StatusEffectController::class);
