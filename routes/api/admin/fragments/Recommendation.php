<?php

use App\Http\Controllers\Admin\AdminRecommendationController;
use Illuminate\Support\Facades\Route;

Route::resource('recommendation', AdminRecommendationController::class);
