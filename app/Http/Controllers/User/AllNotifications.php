<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AllNotifications extends Controller
{
    public function get()
    {
        $userId = Auth::id();

        if (!$userId) {
            return [];
        }

        return DB::table('notifications')
            ->selectRaw('(SELECT COUNT(*) FROM notifications WHERE user_id = ? AND viewed = 0) as notification_count', [$userId])
            ->selectRaw('(SELECT COUNT(*) FROM messages WHERE user_id = ? AND viewed = 0) as message_count', [$userId])
            ->first();
    }
}
