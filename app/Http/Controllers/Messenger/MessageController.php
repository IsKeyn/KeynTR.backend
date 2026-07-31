<?php

namespace App\Http\Controllers\Messenger;

use App\Events\Messenger\LastReadCompanionMessage;
use App\Events\Messenger\Typing;
use App\Http\Controllers\Controller;
use App\Http\Resources\Messenger\ListResource;
use App\Http\Resources\UserPublicResource;
use App\Models\Messenger\Chat;
use App\Models\Messenger\Message;
use App\Services\Cache\Messenger\ChatCacheService;
use App\Services\Messenger\MessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends Controller
{
    /**
     * Получение чатов авторизованного пользователя и сообщения к последнему чату или выбранному пользователю
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChats(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()
                ->json(['error' => __('auth.you_are_not_auth_user')])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        $cacheKey = ChatCacheService::LIST_PREFIX . '_' . $user->id;

        if ($request->companionId) {
            $cacheKey .= '_' . $request->companionId;
        }

        return Cache::remember($cacheKey, ChatCacheService::TIME, function () use ($request, $user) {
            $chats = $user->chats()
                ->with([
                    'lastMessage.user:id,name',
                    'users' => function ($query) {
                        $query->select('users.id', 'users.name')
                            ->withPivot('last_read_message_id');
                    },
                    'users.avatar',
                ])
                ->orderByDesc('last_message_at')
                ->get();

            $result = [];

            foreach ($chats as $key => $chat) {
                // 1. Находим данные текущего пользователя в этом чате (из pivot)
                $currentUserPivot = $chat->users->firstWhere('id', $user->id);
                $myLastReadId = $currentUserPivot ? (int)$currentUserPivot->pivot->last_read_message_id : 0;

                // 2. Находим собеседника
                $companion = $chat->users->firstWhere('id', '!=', $user->id);
                $companionLastReadMessageId = $companion ? (int)$companion->pivot->last_read_message_id : null;

                // 3. Считаем непрочитанные сообщения
                // (Сообщения, где отправитель не я, и ID сообщения больше моего last_read)
                $unreadCount = Message::where('chat_id', $chat->id)
                    ->where('user_id', '!=', $user->id)
                    ->where('id', '>', $myLastReadId)
                    ->count();

                $result[$key] = [
                    'chat' => $chat,
                    'companion' => UserPublicResource::make($companion),
                    'companion_last_read_message_id' => $companionLastReadMessageId,
                    'unread_count' => $unreadCount,
                ];

                $messages = null;

                if ($request->companionId) {
                    if ($companion->id === (int)$request->companionId) {
                        $messages = $chat->messages()
                            ->with('user:id,name')
                            //->with('attachments')
                            ->orderBy('id', 'desc')
                            ->limit(20)
                            ->get()
                            ->sortBy('id')
                            ->values();
                    }
                } elseif ($key === 0) {
                    $messages = $chat->messages()
                        ->with('user:id,name')
                        //->with('attachments')
                        ->orderBy('id', 'desc')
                        ->limit(20)
                        ->get()
                        ->sortBy('id')
                        ->values();
                }

                if ($chat->last_message_id) {
                    $chat->users()->updateExistingPivot($user->id, [
                        'last_read_message_id' => $chat->last_message_id
                    ]);
                }

                if ($messages) {
                    $result[$key]['messages'] = ListResource::collection($messages);
                }
            }

            return response()
                ->json($result)
                ->setStatusCode(Response::HTTP_OK);
        });
    }

    /**
     * Получаем сообщения чата
     * Если передан $request->first_message_id, то получаем сообщегтя до переданного id
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getMessages(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()
                ->json(['error' => __('auth.you_are_not_auth_user')])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        if (!$request->chat_id) {
            return response()
                ->json(['error' => __('messenger.chat.not_received_chat_id')])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        $messages = Message::query()
            ->where('chat_id', $request->chat_id);

        if ($request->first_message_id) {
            $messages->where('id', '<', $request->first_message_id);
        }

        $messages = $messages->orderByDesc('id')->limit(20)->get();

        return ListResource::collection($messages->reverse()->values());
    }

    /**
     * Создаем WebSocked событие о том, что пользователь печает
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function typing(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()
                ->json(['error' => __('auth.you_are_not_auth_user')])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        if (!$request->chat_id) {
            return response()
                ->json(['error' => __('messenger.chat.not_received_chat_id')])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        $chat = Chat::query()
            ->findById($request->chat_id)
            ->with(['users'])
            ->first();

        foreach ($chat->users as $charUser) {
            if ($charUser->id !== $user->id) {

                $dataForSend = [
                    'chat' => $chat,
                    'companion' => UserPublicResource::make($user),
                ];

                Typing::dispatch($charUser->id, $dataForSend);
            }
        }
    }

    /**
     * Отмечаем сообщения, как прочитанные
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setMessageAsRead(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()
                ->json(['error' => __('auth.you_are_not_auth_user')])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        if (!$request->chat_id) {
            return response()
                ->json(['error' => __('messenger.chat.not_received_chat_id')])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        $chat = Chat::query()
            ->with(['users'])
            ->find($request->chat_id);

        if (!$chat) {
            return response()
                ->json(['error' => __('messenger.chat.not_found')])
                ->setStatusCode(Response::HTTP_NOT_FOUND);
        }

        // Проверяем, что пользователь — участник чата
        if (!$chat->users->contains('id', $user->id)) {
            return response()
                ->json(['error' => __('messenger.chat.not_a_member')])
                ->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        // Если в чате нет сообщений — ничего не делаем
        if (!$chat->last_message_id) {
            return response()->json()->setStatusCode(Response::HTTP_OK);
        }

        // Обновляем last_read_message_id в pivot-таблице
        $lastMessageId = $request->message_id ? $request->message_id : $chat->last_message_id;

        $chat->users()->updateExistingPivot($user->id, [
            'last_read_message_id' => $lastMessageId,
        ]);

        // Отправляем событие через WebSocket и передаем ID последнего прочитанного сообщения
        foreach ($chat->users as $chatUser) {
            $chatCacheService = app(ChatCacheService::class);
            $chatCacheService->clearUserListCache($chatUser);

            if ($chatUser->id != $user->id) {
                MessageService::notificationSend($user->id);

                LastReadCompanionMessage::dispatch(
                    $chatUser->id,
                    [
                        'chat_id' => $chat->id,
                        'last_message_id' => $lastMessageId,
                    ]
                );
            }
        }

        return response()
            ->json(['last_read_message_id' => $lastMessageId])
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Сохранение сообщения в базе данных
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()
                ->json(['error' => __('auth.you_are_not_auth_user')])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        $validated = $request->validate([
            'message'           => 'required|string|max:4000',
            'chat_id'           => 'nullable|integer|exists:chats,id',
            'recipient_user_id' => 'nullable|integer|exists:users,id',
            'reply_to_id'       => 'nullable|integer|exists:messages,id',
            'type'              => 'in:text,image,file',
        ]);

        $authUser = $request->user();

        // Должен быть указан либо chat_id, либо recipient_user_id
        if (empty($validated['chat_id']) && empty($validated['recipient_user_id'])) {
            return response()
                ->json(['error' => __('messenger.message.you_are_not_auth_user')])
                ->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Нельзя отправить сообщение самому себе
        if (
            !empty($validated['recipient_user_id']) &&
            $validated['recipient_user_id'] === $authUser->id
        ) {
            return response()
                ->json(['error' => __('messenger.message.cant_send_message_to_myself')])
                ->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /*
        // 1. Проверка, что получатель не заблокировал отправителя
        if (!empty($validated['recipient_user_id'])) {
            $isBlocked = DB::table('blocks')
                ->where('user_id', $validated['recipient_user_id'])
                ->where('blocked_user_id', $authUser->id)
                ->exists();

            if ($isBlocked) {
                abort(403, 'Пользователь заблокировал вас');
            }
        }

        // 2. Проверка, что пользователь не пишет в групповой чат, из которого его исключили
        if (!empty($validated['chat_id'])) {
            $chat = Chat::find($validated['chat_id']);
            if ($chat->type === 'group') {
                // Доп. проверки для групповых чатов
            }
        }
        */

        [$chat, $message] = DB::transaction(function () use ($validated, $authUser) {
            // 1. Получаем или создаем чат
            $chat = $this->resolveChat($validated, $authUser);

            // 2. Создаем сообщение
            $message = Message::create([
                'chat_id'     => $chat->id,
                'user_id'     => $authUser->id,
                'reply_to_id' => $validated['reply_to_id'] ?? null,
                'type'        => $validated['type'] ?? 'text',
                'body'        => $validated['message'],
            ]);

            // 3. Обновляем "снимок" последнего сообщения в чате
            $chat->update([
                'last_message_id' => $message->id,
                'last_message_at' => $message->created_at,
            ]);

            // 4. Помечаем сообщение как прочитанное для отправителя
            //    (он же его видел, когда нажимал "отправить")
            $chat->users()->updateExistingPivot($authUser->id, [
                'last_read_message_id' => $message->id,
            ]);

            return [$chat, $message];
        });

        // 5. Загружаем данные для ответа
        $message->load('user:id,name');

        return response()
            ->json([
                'chat_id' => $chat->id,
                'message' => $message,
            ])
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Находит существующий чат или создает новый.
     * Использует блокировку для защиты от race condition.
     *
     * @param array $data
     * @param $authUser
     * @return Chat
     */
    private function resolveChat(array $data, $authUser): Chat
    {
        // Если фронт прислал chat_id
        if (!empty($data['chat_id'])) {
            $chat = Chat::findOrFail($data['chat_id']);

            // Проверяем, что текущий пользователь — участник этого чата
            $isMember = $chat->users()
                ->where('user_id', $authUser->id)
                ->exists();

            if (!$isMember) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, __('messenger.chat.not_a_member'));
            }

            return $chat;
        }

        // Если фронт прислал recipient_user_id
        $recipientId = $data['recipient_user_id'];

        // Ключ блокировки должен быть одинаковым для пары пользователей
        // Сортируем ID, чтобы ключ был одинаковым в обоих направлениях
        $pair = [$authUser->id, $recipientId];
        sort($pair);
        $lockKey = "chat:create:{$pair[0]}:{$pair[1]}";

        // Блокировка на 5 секунд
        return Cache::lock($lockKey, 5)->block(5, function () use ($authUser, $recipientId) {

            // Повторная проверка внутри блокировки
            $existingChat = Chat::where('type', 'private')
                ->whereHas('users', fn($q) => $q->where('user_id', $authUser->id))
                ->whereHas('users', fn($q) => $q->where('user_id', $recipientId))
                ->first();

            if ($existingChat) {
                return $existingChat;
            }

            // Создаем новый чат
            $newChat = Chat::create(['type' => 'private']);
            $newChat->users()->attach([$authUser->id, $recipientId]);

            return $newChat;
        });
    }
}
