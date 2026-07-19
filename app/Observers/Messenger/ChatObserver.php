<?php

namespace App\Observers\Messenger;

use App\Models\Messenger\Chat;
use App\Services\Observer\DefaultObserverService;

class ChatObserver
{
    private const CACHE_SERVICE = Chat::CACHE_SERVICE;
    private const SERVICE = Chat::SERVICE;

    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    public function created(Chat $chat)
    {
        $this->additionalActions($chat);

        $this->defaultObserverService->created(
            $chat,
            self::CACHE_SERVICE,
            self::SERVICE,
            false
        );
    }

    public function updated(Chat $chat)
    {
        $this->additionalActions($chat);

        $this->defaultObserverService->updated(
            $chat,
            self::CACHE_SERVICE,
            self::SERVICE,
            true,
            false,
        );
    }

    public function deleted(Chat $chat)
    {
        $this->additionalActions($chat);

        $this->defaultObserverService->deleted(
            $chat,
            self::CACHE_SERVICE,
            self::SERVICE,
            true,
            false,
        );
    }

    public function restored(Chat $chat)
    {
        $this->additionalActions($chat);

        $this->defaultObserverService->restored(
            $chat,
            self::CACHE_SERVICE,
            self::SERVICE,
            true,
            false,
        );
    }

    public function forceDeleted(Chat $chat)
    {
        $this->additionalActions($chat);

        $this->defaultObserverService->forceDeleted(
            $chat,
            self::CACHE_SERVICE
        );
    }

    private function additionalActions($chat)
    {
        $this->clearRelatedCache($chat);
    }

    private function clearRelatedCache($chat)
    {

    }
}
