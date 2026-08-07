<?php

namespace App\Services\BoardGame;

use App\Http\Resources\BoardGame\Items\BgItemBindResource;
use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\Item;
use App\Models\BoardGame\ItemBind;
use App\Services\Cache\BoardGame\BgItemBindCacheService;
use App\Services\Entity\EntityService;
use Illuminate\Support\Facades\Cache;

class ItemService
{
    /**
     * Добавляем предмет в инвентарь игрока
     *
     * @param int $userId
     * @param int  $boardGameId
     * @param int $boardGameItemId
     * @param null $playerId
     * @return mixed
     */
    public static function addToInventory(
        int $userId,
        int $boardGameId,
        int $boardGameItemId,
        int $playerId = null
    )
    {
        $fields = [
            'user_id' => $userId,
            'created_by' => $userId,
            'board_game_id' => $boardGameId,
            'board_game_item_id' => $boardGameItemId,
        ];

        if ($playerId) {
            $fields['bg_player_id'] = $playerId;
        }

        return BoardGameInventory::create($fields);
    }

    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            Item::class,
            Item::CACHE_SERVICE,
            Item::DETAIL_RESOURCE,
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

    public static function itemsInBoardGame($bgId)
    {
        $cacheKey = BgItemBindCacheService::LIST_PREFIX . '_' . $bgId;

        return Cache::remember($cacheKey, BgItemBindCacheService::TIME, function () use ($bgId) {
            $items = ItemBind::query()
                ->active()
                ->findByBoardGame($bgId)
                ->with([
                    'item',
                    'item.titleImage',
                    'item.sound',
                    'item.authorUser',
                ])
                ->get()
                ->sortByDesc(function ($itemBind) {
                    return $itemBind->item?->drop_chance ?? 0;
                })
                ->values();

            return BgItemBindResource::collection($items);
        });
    }
}
