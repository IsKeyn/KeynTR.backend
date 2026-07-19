<?php

namespace App\Services\Messenger;

use App\Models\Messenger\Chat;
use App\Services\Entity\EntityService;

class ChatService
{
    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            Chat::class,
            Chat::CACHE_SERVICE,
            Chat::DETAIL_RESOURCE,
            $id,
            [
                'tags',
                'additionalFields',
                'media',
                'seo',
                'seo.entity',
                'seo.entity.tags',
                'menu',
                'menu.elements',
                'blocks',
            ],
            $forceRefresh,
            $withTrashed,
        );
    }
}
