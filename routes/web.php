<?php

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
