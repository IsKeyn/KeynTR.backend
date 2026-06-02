<?php

use App\Http\Controllers\Admin\AdminPageController;
use Illuminate\Support\Facades\Route;

Route::resource('pages', AdminPageController::class);
