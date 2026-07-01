<?php

use App\Http\Controllers\BoardGame\DiceController;
use Illuminate\Support\Facades\Route;

Route::prefix('dice/')
    ->controller(DiceController::class)
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::post('roll', 'rollDice')->name('roll');
});
