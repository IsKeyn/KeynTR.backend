<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function getCurrentUserMessages()
    {
        $user = Auth::user();

        $messages = $user->allMessages()->sortBy('created_at');

        // Разделение сообщений по группам, где группой является пользователь

        return true;
        dd($messages);
    }
}
