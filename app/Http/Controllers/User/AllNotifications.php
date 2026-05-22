<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;

class AllNotifications extends Controller
{
    public function get()
    {
        return NotificationService::getCount();
    }
}
