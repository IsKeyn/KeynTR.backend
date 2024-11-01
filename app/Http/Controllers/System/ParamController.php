<?php

namespace App\Http\Controllers\System;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ParamController extends Controller
{
    public function getPhpParamValue($paramName) {
        if (!$paramName) {
            return null;
        }

        return ini_get($paramName);
    }
}
