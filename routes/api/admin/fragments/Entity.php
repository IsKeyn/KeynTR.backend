<?php

use App\Http\Controllers\Admin\AdminEntityController;
use Illuminate\Support\Facades\Route;

Route::prefix('entity')->controller(AdminEntityController::class)->group(function () {
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
