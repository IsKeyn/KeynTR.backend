<?php

use App\Http\Controllers\Admin\AdminArticlePagesController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminEntityController;
use App\Http\Controllers\Admin\AdminErrorsPagesController;
use App\Http\Controllers\Admin\AdminMediaPagesController;
use App\Http\Controllers\Admin\AdminMenuPagesController;
use App\Http\Controllers\Admin\AdminMenuTypesPagesController;
use App\Http\Controllers\Admin\YouTubePageController;
use App\Http\Controllers\YouTubeController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

/* Страницы админки */
Route::middleware('auth')->middleware('is_admin')->group(function () {

    Route::name('admin.')->prefix('admin')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');

        Route::resource('articles', AdminArticlePagesController::class);

        Route::name('youtube.')->prefix('youtube')->group(function() {
            Route::get('/', [YouTubePageController::class, 'index'])->name('index');
            Route::get('/fetch-playlists-and-videos', [YouTubeController::class, 'getAllPlaylistsAndVideos'])->name('fetch-playlists-and-videos');
            Route::get('/fetch-last-videos-from-youtube', [YouTubeController::class, 'getLastVideosFromApi'])->name('fetch-last-videos-from-youtube');
        });

        Route::resource('menu-types', AdminMenuTypesPagesController::class);
        Route::resource('menu', AdminMenuPagesController::class);
        Route::resource('errors', AdminErrorsPagesController::class);
        Route::resource('media', AdminMediaPagesController::class);

        Route::name('entity.')
            ->prefix('entity')
            ->controller(AdminEntityController::class)
            ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('{entityName}', 'detail');
            Route::get('{entityName}/add', 'add')->name('add-element');
            Route::get('{entityName}/{id}/edit','edit')->name('edit-element');
            Route::post('{entityName}/store', 'store')->name('store-element');
            Route::post('{entityName}/{id}/update', 'update')->name('update-element');
            Route::post('{entityName}/{id}/store-additional-field', 'storeAdditionalField')->name('store-element-additional-field');
            Route::post('{entityName}/{id}/update-additional-field', 'updateAdditionalField')->name('update-element-additional-field');
            Route::post('{entityName}/{id}/delete-additional-field', 'deleteAdditionalField')->name('delete-element-additional-field');
        });

//        Route::prefix('article')->group(function () {
//            Route::get('', [App\Http\Controllers\Admin\AdminArticlePagesController::class, 'articleMain'])->name('admin.article');
//
//            Route::get('update/{id}', [App\Http\Controllers\Admin\AdminArticlePagesController::class, 'update'])->name('admin.article.update');
//            Route::post('update', [App\Http\Controllers\ArticleController::class, 'update'])->name('article.update');
//        });
    });
});


Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->middleware('auth')->name('home');

Route::get('/get_data_from_youtube', [App\Http\Controllers\YouTubeController::class, 'getDataFromYouTube'])->name('getDataFromYouTube');
Route::get('/last_videos', [App\Http\Controllers\YouTubeController::class, 'getLastVideos']);
Route::get('/get_google_access_token', [App\Http\Controllers\YouTubeController::class, 'googleOAuth2'])->name('googleOAuth2');


Route::get('/googleOAuth', [App\Http\Controllers\YouTubeController::class, 'googleOAuthAuth'])->name('ga');


Route::get('/artisan/migrate', function () {
    $exitCode = Artisan::call('migrate');

    dump($exitCode);
});


Route::get('/artisan/config/clear', function () {
    $exitCode = Artisan::call('config:clear');

    dump($exitCode);
});
