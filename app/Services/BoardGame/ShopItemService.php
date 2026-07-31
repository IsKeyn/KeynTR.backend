<?php

namespace App\Services\BoardGame;

use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\ItemBind;
use App\Models\BoardGame\ShopItem;
use App\Services\Entity\EntityService;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

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

    /**
     * Функция исключает предмет из ивентаря игрока, но создает из него слот в магазине
     *
     * @param $data
     * @return array|\Illuminate\Http\JsonResponse|mixed|string[]
     */
    public function toStore($data)
    {
        $conditionData = PlayerGameService::checkConditions($data->slug);

        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        }

        if (!$data->entity_type) {
            return response()
                ->json(['error' => __('boardGame.not_received_type')])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        if (!$data->id) {
            return response()
                ->json(['error' => __('boardGame.player.inventory.not_received_inventory_id')])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        /*
         * Получаем информацию из инвентаря пользователя о предмете,
         * предмет должен участвовать в текущей настольной игре
         */
        $inventoryItem = BoardGameInventory::query()
            ->where('id', $data->id)
            ->findByPlayer($conditionData['player']->id)
            ->where('has_used', false)
            ->with([
                'itemBind',
                'itemBind.item',
            ])
            ->first();

        if (!$inventoryItem) {
            return response()
                ->json(['error' => __('boardGame.player.inventory.not_found')])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        $soldItemsCount = ShopItem::query()
            ->where('bg_player_id', $conditionData['player']->id)
            ->where('status', ShopItem::STATUS_ON_SALE)
            ->count();

        if ($soldItemsCount >= 3) {
            return response()
                ->json(['error' => __('boardGame.shop.to_many_products_for_sale')])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        return DB::transaction(function () use ($inventoryItem, $conditionData, $data) {
            $shopFields = [
                'bg_player_id' => $inventoryItem->bg_player_id,
                'user_id' => $inventoryItem->user_id,
                'board_game_id' => $inventoryItem->board_game_id,
                'entity_type' => $data->entity_type,
                'entity_id' => $inventoryItem->itemBind->id,
                'status' => ShopItem::STATUS_ON_SALE,
                'active' => true,
            ];

            $createNewShopItemRes = ShopItem::create($shopFields);

            if (!$createNewShopItemRes) {
                return response()
                    ->json(['error' => __('boardGame.shop.create_failed')])
                    ->setStatusCode(Response::HTTP_BAD_REQUEST);
            }

            $inventoryItemUpdate = $inventoryItem->update(['active' => false]);

            if (!$inventoryItemUpdate) {
                return response()
                    ->json(['error' => __('boardGame.player.inventory.update_failed')])
                    ->setStatusCode(Response::HTTP_BAD_REQUEST);
            }

            LogService::addLog(
                $conditionData['user']->id,
                $conditionData['boardGame']->id,
                __('boardGame.shop.put_for_sale', [
                    'name' => $inventoryItem->itemBind->item->name
                ]),
                $conditionData['player']->id,
            );

            return response()
                ->json(['message' => __('boardGame.shop.success_saved')])
                ->setStatusCode(Response::HTTP_CREATED);
        });
    }
}
