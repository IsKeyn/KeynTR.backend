<?php

use App\Http\Controllers\Admin\AdminMediaGroupController;
use Illuminate\Support\Facades\Route;

Route::resource('media-group', AdminMediaGroupController::class);
