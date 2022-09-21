<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\YouTubeController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::name('api.')->group(function() {
    Route::get('article/get', [ArticleController::class, 'get']);
    Route::post('article/get', [ArticleController::class, 'getByFilter']);

    Route::post('comment/getList', [CommentsController::class, 'getList']);
    Route::post('comment/add', [CommentsController::class, 'add']);

    Route::post('subscription/add', [SubscriptionController::class, 'add']);

    Route::post('youtube/lastVideo', [YouTubeController::class, 'getLastVideos']);
});
