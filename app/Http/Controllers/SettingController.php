<?php

namespace App\Http\Controllers;

use App\Services\SettingService;

class SettingController extends Controller
{
    public function get() {
        return SettingService::getList();
    }
}
