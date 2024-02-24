<?php

namespace App\Services;

use App\Models\UserActionLog;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;

class UserActionLogService extends ServiceProvider
{
    public static function set(Request $request, $userActionLogParams) {
        $actionLog = UserActionLog::create($userActionLogParams);
        UserAgentService::setData($request, $actionLog);
    }
}
