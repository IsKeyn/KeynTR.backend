<?php

use App\Http\Controllers\System\ParamController;
use Illuminate\Support\Facades\Route;

Route::get('param-value/{paramName}', [ParamController::class, 'getPhpParamValue'])->name('value');
