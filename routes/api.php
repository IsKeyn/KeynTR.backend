<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\ErrorController;
use App\Http\Controllers\FormResultController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\YouTubeController;
use App\Services\SearchService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::name('api.')->group(function() {
    Route::get('csrf', function () {
        return csrf_token();
    });

    Route::name('auth.')->prefix('auth/')->middleware('guest:api')->group(function() {
        Route::post('login', [LoginController::class, 'login'])->name('login');
        Route::post('register', [RegisterController::class, 'register'])->name('register');
        Route::post('forgot-password', [ResetPasswordController::class, 'sendResetLink'])->name('password.email');
        Route::post('reset-password', [ResetPasswordController::class, 'resetPassword'])->name('password.update');
    });

    Route::get('article/get', [ArticleController::class, 'get']);
    Route::post('article/get', [ArticleController::class, 'getByFilter'])->name('getByFilter');

    Route::post('comment/getList', [CommentsController::class, 'getList']);
    Route::post('comment/add', [CommentsController::class, 'add']);

    Route::resource('game', GameController::class);
    Route::post('game/list', [GameController::class, 'getList']);
    Route::post('game/{query}', [GameController::class, 'getGame']);

    Route::post('subscription/add', [SubscriptionController::class, 'add']);

    Route::post('youtube/lastVideo', [YouTubeController::class, 'getLastVideos']);

    Route::name('menu.')->prefix('menu/')->group(function() {
        Route::post('get', [MenuController::class, 'getMenuElements']);
        Route::post('getArticlesMenu', [MenuController::class, 'getArticlesMenu']);
    });

    Route::name('search.')->prefix('search/')->group(function() {
        Route::post('{query}', [SearchService::class, 'search']);
    });

    Route::name('error')->prefix('error/')->group(function() {
        Route::post('set', [ErrorController::class, 'set'])->name('set');
        Route::post('get', [ErrorController::class, 'get'])->name('get');
    });

    Route::name('form')->prefix('form/')->group(function() {
        Route::post('set', [FormResultController::class, 'set'])->name('set');
    });
});
