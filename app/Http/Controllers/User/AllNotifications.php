<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User\Notification;
use Illuminate\Support\Facades\Auth;

class AllNotifications extends Controller
{
    public function get(Notification $notification)
    {
        $user = Auth::user();

        if ($user) {
            return [
                'notification_count' => $notification::where('user_id', $user->id)->where('viewed', false)->count(),
                'message_count' => $user->allMessages()->where('viewed', false)->count(),
            ];
        }
    }
}
