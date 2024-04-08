<?php

use App\Http\Controllers\Admin\AdminArticlePagesController;
use App\Http\Controllers\Admin\AdminEntityController;
use App\Http\Controllers\Admin\AdminMediaPagesController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
 * Подтверждение регистрации
 */
Route::get('/auth/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    $user = $request->user();

    return UserResource::make($user);
})->middleware(['auth:sanctum', 'signed'])->name('verification.verify');

Route::name('api.')->group(function() {
    Route::name('auth.')->prefix('auth/')->middleware('auth:sanctum')->group(function() {
        Route::get('user', [UserController::class, 'authUser'])->name('user');
        Route::get('logout', [LoginController::class, 'logout'])->name('logout');
        Route::post('verification-notification', [UserController::class, 'sendVerificationNotification'])->middleware(['throttle:6,1'])->name('verification.send');;
    });

    Route::name('admin.')->prefix('admin/')->middleware('auth:sanctum, is_admin')->group(function() {
        Route::name('entity.')
            ->prefix('entity')
            ->controller(AdminEntityController::class)
            ->group(function () {
                Route::get('{entityName}', 'index')->name('index');
                Route::post('{entityName}', 'store')->name('store');
                Route::put('{entityName}/{id}', 'update')->name('update');
                Route::delete('{entityName}/{id}', 'destroy')->name('destroy');
                Route::get('{entityName}/{id}/edit','edit')->name('edit');

//                Route::get('{entityName}', 'detail');
//                Route::get('{entityName}/add', 'add')->name('add-element');



                Route::post('{entityName}/{id}/store-additional-field', 'storeAdditionalField')->name('store-element-additional-field');
                Route::post('{entityName}/{id}/update-additional-field', 'updateAdditionalField')->name('update-element-additional-field');
                Route::post('{entityName}/{id}/delete-additional-field', 'deleteAdditionalField')->name('delete-element-additional-field');
            });
        Route::resource('media', AdminMediaPagesController::class);
        Route::name('media.')->prefix('media/')->group(function() {
            Route::post('multi-store', [AdminMediaPagesController::class, 'multiStore'])->name('multi-store');
        });

        Route::resource('articles', AdminArticlePagesController::class);
    });
});
