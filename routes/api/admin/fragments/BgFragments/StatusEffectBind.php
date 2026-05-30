<?php

use App\Http\Controllers\Admin\BoardGame\StatusEffectBindController;
use Illuminate\Support\Facades\Route;

Route::prefix('StatusEffectBind/')->controller(StatusEffectBindController::class)->group(function() {
    Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
    Route::post('{statusEffectBind}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{statusEffectBind}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
});
Route::resource('StatusEffectBind', StatusEffectBindController::class)
    ->parameters(['StatusEffectBind' => 'statusEffectBind']);
