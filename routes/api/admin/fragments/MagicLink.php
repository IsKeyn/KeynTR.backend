<?php

use App\Http\Controllers\User\MagicLinkController;
use Illuminate\Support\Facades\Route;

Route::prefix('magical-link/')->controller(MagicLinkController::class)->group(function() {
    Route::get('generate/{userId}', 'createLink')->name('generate');
});
