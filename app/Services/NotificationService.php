<?php

namespace App\Services;

use App\Models\User\Notification;

class NotificationService
{
    public static function set($fields)
    {
        return Notification::create($fields);
    }
}
