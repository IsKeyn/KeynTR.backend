<?php

use App\Http\Controllers\Admin\AdminBoardGameController;
use App\Http\Controllers\Admin\AdminGameController;
use App\Http\Controllers\Admin\AdminMediaGroupController;
use App\Http\Controllers\Admin\AdminMovieController;
use App\Http\Controllers\Admin\AdminPageController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\BoardGame\BoardGameController;
use App\Http\Controllers\BoardGame\BoardGameInventoryController;
use App\Http\Controllers\BoardGame\BoardGamePlayerController;
use App\Http\Controllers\BoardGame\DiceController;
use App\Http\Controllers\BoardGame\LogController;
use App\Http\Controllers\BoardGame\PositionController;
use App\Http\Controllers\BoardGame\BoardGameItemController;
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
use App\Http\Controllers\System\ParamController;
use App\Http\Controllers\TagsController;
use App\Http\Controllers\TwitchController;
use App\Http\Controllers\User\NotificationController;
use App\Http\Controllers\ViewsLogController;
use App\Http\Controllers\VotesLogController;
use App\Http\Controllers\YouTubeController;
use App\Services\SearchService;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminArticlePagesController;
use App\Http\Controllers\Admin\AdminEntityController;
use App\Http\Controllers\Admin\AdminMediaPagesController;
use App\Http\Controllers\Admin\AdminSlideController;
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

    // Авторизация и стандартные действия не авторизированного пользователя
    Route::prefix('auth/')->name('auth.')->middleware('guest:api')->group(function() {
        Route::post('login', [LoginController::class, 'login'])->name('login');

        Route::controller(RegisterController::class)->group(function() {
            Route::post('register', 'register')->name('register');
            Route::post('forgot-password', 'sendResetLink')->name('send-reset-link');
            Route::post('reset-password', 'resetPassword')->name('reset-password');
        });
    });

//    Route::get('user', [UserController::class, 'authUser'])->name('user'); // TODO где используется данный роут?


    // Действия авторизированного пользователя
    Route::prefix('auth/')->name('auth.')->middleware('auth:sanctum')->group(function() {
        Route::get('logout', [LoginController::class, 'logout'])->name('logout');

        Route::controller(UserController::class)->group(function() {
            Route::get('user', 'authUser')->name('user');
            Route::post('verification-notification', 'sendVerificationNotification')
                ->middleware(['throttle:6,1'])->name('verification.send');
            Route::post('setAvatar', 'setAvatar')->name('set-avatar');
            Route::post('update-profile', 'updateProfile')->name('update-profile');
        });

        Route::controller(NotificationController::class)->prefix('notification/')->name('notification')->group(function () {
            Route::get('get', 'GetCurrentUserNotifications')->name('GetCurrentUserNotifications');
            Route::get('getCount', 'GetCountUserNotifications')->name('GetCountUserNotifications');
            Route::post('set', 'set')->name('set');
            Route::post('set-viewed', 'SetViewed')->name('set-viewed');
        });
    });


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


    // Работа с сущностью board-game
    /* TODO для сущностей, которые требует авторизации добавить middleware('auth:sanctum') */
    Route::prefix('board-game/')->name('.board-game')->group(function() {
        Route::get('get/{slug}', [BoardGameController::class, 'getBySlug'])->name('get-by-slug');
        Route::get('get-list', [BoardGameController::class, 'getList'])->name('get-list');
        Route::get('getBoardInfo', [BoardGameController::class, 'getBoardInfo'])->name('get-board-info');
        Route::get('getItemAndInventory', [BoardGameController::class, 'getItemAndInventory'])->name('get-item-and-inventory');
        Route::get('getStreamersOnline', [BoardGameController::class, 'getStreamersOnline'])->name('streamers-online');
        Route::post('roll-dice', [DiceController::class, 'rollDice'])->name('roll-dice');

        Route::prefix('player/')->controller(BoardGamePlayerController::class)->name('.player')->group(function() {
            Route::get('get/{id}', 'get')->name('get');
            Route::get('list', 'list')->name('list');
            Route::post('add', 'add')->name('add');
            Route::post('updatedPoints', 'updatedPoints')->name('update-points');
        });

        Route::prefix('log/')->controller(LogController::class)->name('.log')->group(function() {
            Route::post('add', 'add')->name('add');
            Route::get('list', 'getLogListById')->name('list');
        });

        Route::prefix('position/')->controller(PositionController::class)->name('.position')->group(function() {
            Route::post('add', 'add')->name('add');
        });

        Route::prefix('items/')->controller(BoardGameItemController::class)->name('.items')->group(function() {
            Route::get('list', 'list')->name('list');
        });

        Route::prefix('inventory/')->controller(BoardGameInventoryController::class)->name('.inventory')->group(function() {
            Route::post('add', 'add')->name('add');
            Route::post('list', 'list')->name('list');
            Route::delete('destroy', 'destroy')->name('destroy');
            Route::post('use', 'useItem')->name('use-item');
        });
    });

    // Действия в админке
    Route::prefix('admin/')->name('admin.')->middleware(['auth:sanctum', 'is_admin'])->group(function() {
        Route::prefix('BoardGame')->name('BoardGame.')->group(function () {
            Route::resource('BoardGameItem', BoardGameItemController::class);
            Route::resource('BoardGameInventory', BoardGameInventoryController::class);

            Route::controller(AdminBoardGameController::class)->group(function () {
                Route::get('{entityName}', 'index')->name('index');
                Route::post('{entityName}', 'store')->name('store');
                Route::put('{entityName}/{id}', 'update')->name('update');
                Route::delete('{entityName}/{id}', 'destroy')->name('destroy');
                Route::get('{entityName}/{id}/edit','edit')->name('edit');

                Route::post('{entityName}/{id}/store-additional-field', 'storeAdditionalField')->name('store-element-additional-field');
                Route::post('{entityName}/{id}/update-additional-field', 'updateAdditionalField')->name('update-element-additional-field');
                Route::post('{entityName}/{id}/delete-additional-field', 'deleteAdditionalField')->name('delete-element-additional-field');
            });
        });

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
        });

        Route::resource('media', AdminMediaPagesController::class);
        Route::prefix('media/')->controller(AdminMediaPagesController::class)->name('media.')->group(function() {
            Route::post('multi-store', 'multiStore')->name('multi-store');
        });

        Route::resource('pages', AdminPageController::class);
        Route::resource('articles', AdminArticlePagesController::class);
        Route::resource('slides', AdminSlideController::class);

        Route::get('game/get-additional-data', [AdminGameController::class, 'getAdditionalData'])->name('game.get-additional-data');
        Route::get('movie/get-additional-data', [AdminMovieController::class, 'getAdditionalData'])->name('movie.get-additional-data');
        Route::resource('game', AdminGameController::class);
        Route::resource('movie', AdminMovieController::class);
        Route::resource('media-group', AdminMediaGroupController::class);

        Route::get('param-value/{paramName}', [ParamController::class, 'getPhpParamValue'])->name('param.value');
    });
});

Route::get('/auth/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    $user = $request->user();

    return UserResource::make($user);
})->middleware(['auth:sanctum', 'signed'])->name('verification.verify');

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');
