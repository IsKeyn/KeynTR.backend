<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\Shop\BgShopItemItemResource;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\ItemBind;
use App\Models\BoardGame\ShopItem;
use App\Services\BoardGame\LogService;
use App\Services\BoardGame\PlayerGameService;
use App\Services\Cache\BoardGame\BgShopItemCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ShopItemController extends Controller
{
    /**
     * Список товаров в магазине текущего ивента
     *
     * @param Request $request
     * @param $slug
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getList(
        Request $request,
        $slug
    )
    {
        if (!$slug) {
            return response()
                ->json(['error' => __('boardGame.not_received_slug')])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        if (!$request->entity_type) {
            return response()
                ->json(['error' => __('boardGame.not_received_type')])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        $cacheKey = BgShopItemCacheService::LIST_PREFIX . '_' . $slug . '_' . $request->entity_type;
        $time = BgShopItemCacheService::TIME;

        return Cache::remember($cacheKey, $time, function () use ($slug, $request) {
            $bgId = BoardGame::query()->findBySlug($slug)->value('id');

            if (!$bgId) {
                return response()
                    ->json(['error' => __('boardGame.not_found')])
                    ->setStatusCode(Response::HTTP_BAD_REQUEST);
            }

            $shopItems = ShopItem::query()
                ->findByBoardGame($bgId)
            ;

            if ($request->entity_type === ItemBind::class) {
                $shopItems->where('entity_type', ItemBind::class);

                $shopItems->with([
                    'entity',
                    'entity.item',
                    'entity.item.titleImage',
                    'entity.item.sound',
                    'entity.item.authorUser',
                    'user',
                    'user.avatar',
                ]);
            }

            $shopItemsRes = $shopItems->orderBy('id', 'desc')->get();

            if ($request->entity_type === ItemBind::class) {
                $soldItems = $shopItemsRes->where('status', ShopItem::STATUS_SOLD);

                $storeProfit = 0;

                foreach ($soldItems as $soldItem) {
                    $storeProfit += round($soldItem->entity->item->price * 0.35);
                }
            }

            return response()
                ->json([
                    'storeProfit' => $storeProfit,
                    'items' => BgShopItemItemResource::collection($shopItemsRes->where('status', ShopItem::STATUS_ON_SALE)),
                ])
                ->setStatusCode(Response::HTTP_OK);
        });
    }

    /**
     * Функция снимает товар из магазина, помещает его в инвентарь игрока, логирует данные действия
     * и меняет количество очков игроков
     *
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse|mixed|string[]
     */
    public function buy(Request $request)
    {
        if (!$request->slug) {
            return response()
                ->json(['error' => __('boardGame.not_received_slug')])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        if (!$request->entity_type) {
            return response()
                ->json(['error' => __('notReceived.not_received_entity_type')])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        $conditionData = PlayerGameService::checkConditions($request->slug);

        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        }

        if (!$request->id) {
            return response()
                ->json(['error' => __('notReceived.not_received_id')])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        // Получаем товар магазина
        $shopItem = ShopItem::query()
            ->findById($request->id)
            ->where('status', ShopItem::STATUS_ON_SALE)
            ->with([
                'seller',
                'entity',
            ]);

        if ($request->entity_type) {
            $shopItem->with([
                'entity.item',
            ]);
        }

        $shopItem = $shopItem->first();

        if (!$shopItem) {
            return response()
                ->json(['error' => __('boardGame.shop.shop_item_not_found')])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        $price = 0;

        if ($request->entity_type && $shopItem->entity->item->price) {
            // Проверяем достаточно ли у игрока очков, чтобы купить предмет
            $price = round($shopItem->entity->item->price + ($shopItem->entity->item->price * 0.35));

            if ($conditionData['player']->points < $price) {
                return response()
                    ->json(['error' => __('boardGame.shop.not_enough_points')])
                    ->setStatusCode(Response::HTTP_BAD_REQUEST);
            }
        }

        return DB::transaction(function () use ($conditionData, $shopItem, $price) {
            // Снимаем очки за покупку у игрока
            $conditionData['player']->update(['points' => $conditionData['player']->points - $price]);

            // Добавляем очки продавшему предмет
            $shopItem->seller->update(['points' => $shopItem->seller->points + $shopItem->entity->item->price]);

            // Добавляем предмет в инвентарь игрока
            $itemFields = [
                'user_id' => $conditionData['player']->user_id,
                'bg_player_id' => $conditionData['player']->id,
                'board_game_id' => $conditionData['boardGame']->id,
                'board_game_item_id' => $shopItem->entity_id,
                'has_used' => false,
                'active' => true,
                'created_by' => $conditionData['player']->user_id,
            ];

            $createNewInventoryItemRes = BoardGameInventory::create($itemFields);

            if (!$createNewInventoryItemRes) {
                return response()
                    ->json(['error' => __('boardGame.player.inventory.create_failed')])
                    ->setStatusCode(Response::HTTP_BAD_REQUEST);
            }

            // Снимаем товар из продажи в магазине
            $shopItem->update([
                'status' => ShopItem::STATUS_SOLD,
                'bought_by_player_id' => $conditionData['player']->id,
            ]);

            // Записываем в логи, что игрок купил предмет и потратил очки
            LogService::addLog(
                $conditionData['user']->id,
                $conditionData['boardGame']->id,
                __('boardGame.shop.buy_item_log', [
                    'name' => $shopItem->entity->item->name,
                    'points' => $price,
                ]),
                $conditionData['player']->id,
            );

            // Записываем в логи, что игрок продал предмет и получил очки
            LogService::addLog(
                $shopItem->seller->user_id,
                $conditionData['boardGame']->id,
                __('boardGame.shop.sell_item_log', [
                    'name' => $shopItem->entity->item->name,
                    'points' => $shopItem->entity->item->price,
                ]),
                $shopItem->seller->id,
            );

            return response()
                ->json([
                    __('boardGame.shop.successful_purchase', [
                        'name' => $shopItem->entity->item->name,
                        'points' => $price,
                    ]),
                ])
                ->setStatusCode(Response::HTTP_CREATED);
        });
    }

    public function withdrawn(Request $request)
    {
        if (!$request->slug) {
            return response()
                ->json(['error' => __('boardGame.not_received_slug')])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        if (!$request->entity_type) {
            return response()
                ->json(['error' => __('notReceived.not_received_entity_type')])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        $conditionData = PlayerGameService::checkConditions($request->slug);

        if (isset($conditionData['status']) && $conditionData['status'] === 'error') {
            return $conditionData;
        }

        if (!$request->id) {
            return response()
                ->json(['error' => __('notReceived.not_received_id')])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        // Получаем товар магазина
        $shopItem = ShopItem::query()
            ->findById($request->id)
            ->where('status', ShopItem::STATUS_ON_SALE)
            ->with([
                'entity',
            ]);

        if ($request->entity_type) {
            $shopItem->with([
                'entity.item',
            ]);
        }

        $shopItem = $shopItem->first();

        if (!$shopItem) {
            return response()
                ->json(['error' => __('boardGame.shop.shop_item_not_found')])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        return DB::transaction(function () use ($conditionData, $shopItem) {
            // Добавляем предмет в инвентарь игрока
            $itemFields = [
                'user_id' => $conditionData['player']->user_id,
                'bg_player_id' => $conditionData['player']->id,
                'board_game_id' => $conditionData['boardGame']->id,
                'board_game_item_id' => $shopItem->entity_id,
                'has_used' => false,
                'active' => true,
                'created_by' => $conditionData['player']->user_id,
            ];

            $createNewInventoryItemRes = BoardGameInventory::create($itemFields);

            if (!$createNewInventoryItemRes) {
                return response()
                    ->json(['error' => __('boardGame.player.inventory.create_failed')])
                    ->setStatusCode(Response::HTTP_BAD_REQUEST);
            }

            // Снимаем товар из продажи в магазине
            $shopItem->update([
                'status' => ShopItem::STATUS_WITHDRAWN,
            ]);

            // Записываем в логи, что игрок отозвал предмет
            LogService::addLog(
                $conditionData['user']->id,
                $conditionData['boardGame']->id,
                __('boardGame.shop.withdrawn_item', [
                    'name' => $shopItem->entity->item->name,
                ]),
                $conditionData['player']->id,
            );

            return response()
                ->json([
                    __('boardGame.shop.successful_withdrawn', [
                        'name' => $shopItem->entity->item->name,
                    ]),
                ])
                ->setStatusCode(Response::HTTP_CREATED);
        });
    }
}
