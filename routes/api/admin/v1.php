<?php

use App\Http\Controllers\Admin\AdminPersonController;
use App\Http\Controllers\Admin\AdminRecommendationController;
use App\Http\Controllers\Admin\AdminSeriesController;
use App\Http\Controllers\Admin\AdminVersionController;
use App\Http\Controllers\Admin\BoardGame\ItemBindController;
use App\Http\Controllers\Admin\BoardGame\ItemController;
use App\Http\Controllers\Admin\BoardGame\BoardGameController;
use App\Http\Controllers\Admin\Games\GamesApiController;
use App\Http\Controllers\Admin\User\UserController;
use App\Http\Controllers\User\MagicLinkController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminGameController;
use App\Http\Controllers\Admin\AdminMediaGroupController;
use App\Http\Controllers\Admin\AdminMovieController;
use App\Http\Controllers\Admin\AdminPageController;
use App\Http\Controllers\System\ParamController;
use App\Http\Controllers\Admin\AdminArticlePagesController;
use App\Http\Controllers\Admin\AdminEntityController;
use App\Http\Controllers\Admin\AdminMediaPagesController;
use App\Http\Controllers\Admin\AdminSlideController;
use App\Http\Controllers\BoardGame\BoardGameInventoryController;

Route::prefix('admin/')->name('v1.')->middleware(['auth:sanctum', 'is_admin'])->group(function() {
    Route::prefix('entity')->controller(AdminEntityController::class)->name('entity.')->group(function () {
        Route::get('{entityName}', 'index')->name('index');
        Route::post('{entityName}', 'store')->name('store');
        Route::put('{entityName}/{id}', 'update')->name('update');
        Route::delete('{entityName}/{id}', 'destroy')->name('destroy');
        Route::get('{entityName}/{id}/edit','edit')->name('edit');

        Route::post('{entityName}/{id}/store-additional-field', 'storeAdditionalField')->name('store-element-additional-field');
        Route::post('{entityName}/{id}/update-additional-field', 'updateAdditionalField')->name('update-element-additional-field');
        Route::post('{entityName}/{id}/delete-additional-field', 'deleteAdditionalField')->name('delete-element-additional-field');

        Route::prefix('{folder}')->controller(AdminEntityController::class)->name('{folder}.')->group(function () {
            Route::get('{entityName}', 'index')->name('index');
            Route::post('{entityName}', 'store')->name('store');
            Route::put('{entityName}/{id}', 'update')->name('update');
            Route::delete('{entityName}/{id}', 'destroy')->name('destroy');
            Route::get('{entityName}/{id}/edit','edit')->name('edit');

            Route::post('{entityName}/{id}/store-additional-field', 'storeAdditionalField')->name('store-element-additional-field');
            Route::post('{entityName}/{id}/update-additional-field', 'updateAdditionalField')->name('update-element-additional-field');
            Route::post('{entityName}/{id}/delete-additional-field', 'deleteAdditionalField')->name('delete-element-additional-field');
        });

        Route::get('getList','getEntityList')->name('getEntityList');
    });

    Route::prefix('BoardGame')->name('BoardGame.')->group(function () {
        Route::resource('BoardGame', BoardGameController::class);
        Route::resource('Item', ItemController::class);
        Route::resource('ItemBind', ItemBindController::class);
        Route::resource('BoardGameInventory', BoardGameInventoryController::class);
    });

    Route::resource('media', AdminMediaPagesController::class);
    Route::prefix('media/')->controller(AdminMediaPagesController::class)->name('media.')->group(function() {
        Route::post('multi-store', 'multiStore')->name('multi-store');
    });

    Route::resource('pages', AdminPageController::class);
    Route::resource('articles', AdminArticlePagesController::class);
    Route::resource('slides', AdminSlideController::class);
    Route::resource('recommendation', AdminRecommendationController::class);
    Route::prefix('version/')->controller(AdminVersionController::class)->name('version.')->group(function() {
        Route::get('getByEntity', 'getByEntity')->name('get-by-entity');
    });
    Route::resource('version', AdminVersionController::class);

    Route::prefix('game/')->controller(AdminGameController::class)->name('game.')->group(function() {
        Route::post('multi-store', 'multiStore')->name('multi-store');

        Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
        Route::post('{game}/force-delete', 'forceDelete')->name('force-delete');
        Route::post('{game}/recovery', 'recovery')->name('recovery');
    });
    Route::resource('game', AdminGameController::class);

    Route::prefix('series/')->controller(AdminSeriesController::class)->name('series.')->group(function() {
        Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
        Route::post('{series}/force-delete', 'forceDelete')->name('force-delete');
        Route::post('{series}/recovery', 'recovery')->name('recovery');
    });
    Route::resource('series', AdminSeriesController::class);

    Route::prefix('person/')->controller(AdminPersonController::class)->name('person.')->group(function() {
        Route::get('get-additional-data', 'getAdditionalData')->name('get-additional-data');
        Route::post('{person}/force-delete', 'forceDelete')->name('force-delete');
        Route::post('{person}/recovery', 'recovery')->name('recovery');
    });
    Route::resource('person', AdminPersonController::class);

    Route::get('movie/get-additional-data', [AdminMovieController::class, 'getAdditionalData'])->name('movie.get-additional-data');
    Route::resource('movie', AdminMovieController::class);
    Route::resource('media-group', AdminMediaGroupController::class);

    Route::get('param-value/{paramName}', [ParamController::class, 'getPhpParamValue'])->name('param.value');

    Route::prefix('magical-link/')->controller(MagicLinkController::class)->name('magic-link.')->group(function() {
        Route::get('generate/{userId}', 'createLink')->name('generate');
    });

    Route::prefix('user/')->controller(UserController::class)->name('user.')->group(function() {
        Route::get('full-logout/{userId}', 'fullLogout')->name('full-logout');
    });

    Route::prefix('api/')->name('api.')->group(function() {
        Route::prefix('games/')->name('games.')->controller(GamesApiController::class)->group(function() {
            Route::get('search', 'search')->name('search');
            Route::post('check', 'check')->name('check');
            Route::post('add', 'add')->name('add');
        });
    });
});
