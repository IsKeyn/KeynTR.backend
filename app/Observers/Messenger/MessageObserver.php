<?php

namespace App\Observers\Messenger;

use App\Events\Messenger\NewMessage;
use App\Events\NotificationCount;
use App\Http\Resources\Messenger\ListResource;
use App\Http\Resources\UserPublicResource;
use App\Models\Messenger\Chat;
use App\Models\Messenger\Message;
use App\Models\User;
use App\Services\Cache\Messenger\ChatCacheService;
use App\Services\Cache\NotificationCacheService;
use App\Services\Messenger\MessageService;
use App\Services\Observer\DefaultObserverService;
use Illuminate\Support\Facades\Cache;

class MessageObserver
{
    private const CACHE_SERVICE = Message::CACHE_SERVICE;
    private const SERVICE = Message::SERVICE;

    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    public function created(Message $message)
    {
        $this->additionalActions($message);

        $this->defaultObserverService->created(
            $message,
            self::CACHE_SERVICE,
            self::SERVICE,
            false
        );
    }

    public function updated(Message $message)
    {
        $this->additionalActions($message);

        $this->defaultObserverService->updated(
            $message,
            self::CACHE_SERVICE,
            self::SERVICE,
            true,
            false,
        );
    }

    public function deleted(Message $message)
    {
        $this->additionalActions($message);

        $this->defaultObserverService->deleted(
            $message,
            self::CACHE_SERVICE,
            self::SERVICE,
            true,
            false,
        );
    }

    public function restored(Message $message)
    {
        $this->additionalActions($message);

        $this->defaultObserverService->restored(
            $message,
            self::CACHE_SERVICE,
            self::SERVICE,
            true,
            false,
        );
    }

    public function forceDeleted(Message $message)
    {
        $this->additionalActions($message);

        $this->defaultObserverService->forceDeleted(
            $message,
            self::CACHE_SERVICE
        );
    }

    private function additionalActions($message)
    {
        // Отправляем сообщение всем пользователям чата, за исключением отправителя
        $userId = $message->user_id;
        $chat = Chat::query()
            ->findById($message->chat_id)
            ->with([
                'lastMessage.user:id,name',
                'users' => function ($query) {
                    $query->select('users.id', 'users.name')
                        ->withPivot('last_read_message_id');
                },
                'users.avatar',
            ])
            ->first();

        foreach ($chat->users as $user) {
            $chatCacheService = app(ChatCacheService::class);
            $chatCacheService->clearUserListCache($user);
            MessageService::notificationSend($user->id);

            if ($user->id !== $userId) {
                $dataForSend = [
                    'chat' => $chat,
                    'companion' => UserPublicResource::make($chat->users->where('id', '!=', $user->id)->first()),
                    'message' => ListResource::make($message),
                ];

                NewMessage::dispatch($user->id, $dataForSend);
            }
        }

        $this->clearRelatedCache($message);
    }

    private function clearRelatedCache($message)
    {
    }
}
