<?php

use App\Http\Controllers\Admin\Article\ArticleController;
use Illuminate\Support\Facades\Route;

Route::prefix('article/')->controller(ArticleController::class)->group(function() {
    Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
    Route::post('{article}/force-delete', 'forceDelete')->name('force-delete');
    Route::post('{article}/recovery', 'recovery')->name('recovery');
    Route::get('filters', 'getListFilters')->name('filters');
});
Route::resource('article', ArticleController::class);
