<?php

use App\Http\Controllers\Admin\AdminEntityController;
use Illuminate\Support\Facades\Route;

Route::prefix('entity/')->name('v1.')->group(function() {
    Route::get('getList', [AdminEntityController::class, 'getEntityList'])->name('getEntityList');
    Route::get('getFields', [AdminEntityController::class, 'getFields'])->name('getFields');
});
