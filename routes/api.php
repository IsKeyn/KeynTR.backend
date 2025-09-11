<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\ErrorController;
use App\Http\Controllers\FormResultController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MediaGroupController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SlideController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TagsController;
use App\Http\Controllers\ViewsLogController;
use App\Http\Controllers\VotesLogController;
use App\Http\Controllers\YouTubeController;
use App\Services\SearchService;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\UserResource;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

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

/*
 * Подтверждение регистрации
 */

Route::name('api.')->group(function() {
    // Общие
    Route::get('csrf', function () { return csrf_token(); });

    // Действия пользователя
    Route::name('auth.')->group(base_path('routes/api/auth/v1.php'));
    Route::name('user.')->group(base_path('routes/api/user/v1.php'));

    // Ошибки
    Route::prefix('error/')->controller(ErrorController::class)->name('error')->group(function() {
        Route::post('set', 'set')->name('set');
        Route::post('get', 'get')->name('get');
    });

    // Настройки сайта
    Route::prefix('site-settings/')->controller(SettingController::class)->name('site-settings')->group(function() {
        Route::get('get', 'get')->name('get');
    });



    // Работа с меню
    Route::prefix('menu/')->controller(MenuController::class)->name('menu.')->group(function() {
        Route::get('get', 'getMenuElements')->name('get-menu-elements');
        Route::get('getArticlesMenu', 'getArticlesMenu')->name('get-articles-menu');
    });

    // Поиск
    Route::prefix('search/')->controller(SearchService::class)->name('search.')->group(function() {
        Route::get('{query}', 'search')->name('search');
    });

    // Комментарии
    Route::prefix('comment/')->controller(CommentsController::class)->name('comment.')->group(function() {
        Route::get('getList', 'getList')->name('get-list');
        Route::post('add', 'add')->name('add');
    });

    // Подписки
    Route::prefix('subscription/')->controller(SubscriptionController::class)->name('subscription')->group(function() {
        Route::post('add', 'add')->name('add');
    });

    // Формы
    Route::prefix('form/')->controller(FormResultController::class)->name('form')->group(function() {
        Route::post('set', 'set')->name('set');
    });


    // Работа с сущностью media
    Route::prefix('media/')->controller(MediaController::class)->name('media')->group(function() {
        Route::post('get', 'getByFilter')->name('get-by-filter');
        Route::get('get/{media}', 'mediaById')->name('get-detail');
    });

    // Работа с сущностью mediaGroup
    Route::prefix('media-group/')->controller(MediaGroupController::class)->name('media-group')->group(function() {
        Route::post('get', 'getByFilter')->name('get-by-filter');
    });

    // Работа с сущностю tag
    Route::prefix('tag/')->controller(TagsController::class)->name('tag')->group(function() {
        Route::get('get/{type}', 'index')->name('get');
    });

    // Работа с сущностью vote
    Route::prefix('vote/')->controller(VotesLogController::class)->name('vote')->group(function() {
        Route::post('set', 'setLike')->name('set-like');
        Route::post('unset', 'unsetLike')->name('unset-like');
    });

    // Работа с сущностью views
    Route::prefix('views/')->controller(ViewsLogController::class)->name('views')->group(function() {
        Route::post('set', 'setView')->name('set-view');
    });


    // Работа с сущность page
    Route::prefix('page/')->controller(PageController::class)->name('page')->group(function() {
        Route::post('get', 'getList')->name('get-list');
        Route::get('get/getByPath', 'getByPath')->name('get-by-path');
    });


    // Работа с сущностью article
    Route::prefix('article/')->controller(ArticleController::class)->name('article')->group(function() {
        Route::post('get', 'getList')->name('get-list');
        Route::get('get/{slug}', 'getBySlug')->name('get-by-slug');
        Route::get('get/id/{id}', 'getById')->name('get-by-id');
    });

    // Работа с сущностью social
    Route::prefix('social/')->controller(SocialController::class)->name('.social')->group(function() {
        Route::get('list', 'getList')->name('social-list');
        Route::get('{game:slug}', 'getSocial')->name('get-social');
    });

    Route::prefix('slide/')->controller(SlideController::class)->name('.slide')->group(function() {
//        Route::get('list', 'getList')->name('slide-list');
        Route::get('listByType', 'getSlideByType')->name('slide-list-by-type');
//        Route::get('{slide:slug}', 'getSlide')->name('get-slide');
    });

    // Работа с сущностью game
    //    Route::resource('game', GameController::class);
    Route::prefix('game/')->controller(GameController::class)->name('.game')->group(function() {
        Route::get('list', 'getList')->name('game-list');
        Route::get('{game:slug}', 'getGame')->name('get-game');
    });

    // Работа с сущностью movie
    Route::prefix('movie/')->controller(MovieController::class)->name('.movie')->group(function() {
        Route::get('list', 'getList')->name('movie-list');
        Route::get('{movie:slug}', 'getMovie')->name('get-movie');
    });


    // Работа с сущностью youtube
    Route::prefix('youtube/')->controller(YouTubeController::class)->name('.youtube')->group(function() {
        Route::post('lastVideo', 'getLastVideos')->name('get-last-videos');
    });

    // Работа с настольной игрой
    Route::name('board-game.')->group(base_path('routes/api/board-game/v1.php'));
    Route::name('board-game.')->group(base_path('routes/api/board-game/v2.php'));

    // Действия в админке
    Route::name('admin.')->group(base_path('routes/api/admin/v1.php'));
});

Route::get('/auth/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    $user = $request->user();

    return UserResource::make($user);
})->middleware(['auth:sanctum', 'signed'])->name('verification.verify');

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');
