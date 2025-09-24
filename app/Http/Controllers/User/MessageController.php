<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

use App\Http\Resources\User\MessageResource;
use App\Http\Resources\UserPublicResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function getCurrentUserMessages()
    {
        $user = Auth::user();

        if ($user) {
            $messages = $user->allMessages()->get()->sortBy('created_at');

            $messagesByUsers = [];
            $usersIds = [];

            foreach ($messages as $message) {
                if ($message->created_by !== $user->id) {
                    if (!in_array($message->created_by, $usersIds)) {
                        $usersIds[] = $message->created_by;
                    }

//                    if (isset($messagesByUsers[$message->created_by])) {
                        $messagesByUsers[$message->created_by][] = $message;
//                    }
                } else {
                    if (!in_array($message->recipient, $usersIds)) {
                        $usersIds[] = $message->recipient;
                    }

//                    if (isset($messagesByUsers[$message->recipient])) {
                        $messagesByUsers[$message->recipient][] = $message;
//                    }
                }
            }
        }

        // Разделение сообщений по группам, где группой является пользователь
        $formattedMessages = collect($messagesByUsers)->map(function ($userMessages) {
            return collect($userMessages);
        });

        return [
            'users' => UserPublicResource::collection(User::whereIn('id', $usersIds)->get()),
            'messages' => $messagesByUsers,
        ];
    }
}
