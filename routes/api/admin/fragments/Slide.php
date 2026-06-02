<?php

use App\Http\Controllers\Admin\AdminSlideController;
use Illuminate\Support\Facades\Route;

Route::resource('slides', AdminSlideController::class);
