<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\ShopItem;
use App\Services\Entity\EntityService;

class ShopItemService
{
    /**
     * Получить данные сущности по ID
     *
     * @param $id integer ID сущности
     * @param false $forceRefresh Принудительное обновление кеша
     * @param false $withTrashed С мягко удаленными записями
     * @return mixed
     */
    public static function getById(
        $id,
        $forceRefresh = false,
        $withTrashed = false
    )
    {
        return EntityService::getById(
            ShopItem::class,
            ShopItem::CACHE_SERVICE,
            ShopItem::DETAIL_RESOURCE,
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
