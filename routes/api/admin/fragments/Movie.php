<?php

use App\Http\Controllers\Admin\AdminMovieController;
use Illuminate\Support\Facades\Route;

Route::get('movie/get-additional-data', [AdminMovieController::class, 'getAdditionalData'])->name('get-additional-data');
Route::resource('movie', AdminMovieController::class);
