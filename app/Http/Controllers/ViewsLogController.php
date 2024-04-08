<?php

namespace App\Http\Controllers;

use App\Services\ViewsLogService;
use Illuminate\Http\Request;

class ViewsLogController extends Controller
{
    public function setView(Request $request) {
        return ViewsLogService::set($request);
    }
}
