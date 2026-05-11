<?php

use App\Http\Controllers\Admin\AdminArticlePagesController;
use Illuminate\Support\Facades\Route;

Route::resource('articles', AdminArticlePagesController::class);
