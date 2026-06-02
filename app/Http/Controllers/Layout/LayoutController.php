<?php

namespace App\Http\Controllers\Layout;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use App\Services\User\UserService;
use Illuminate\Http\Request;

class LayoutController extends Controller
{
    public function getData(Request $request)
    {
        return [
            'user' => UserService::getAuthUser($request),
            'settings' => SettingService::getList(),
        ];
    }
}
