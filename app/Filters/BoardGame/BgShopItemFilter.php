<?php
namespace App\Filters\BoardGame;

use App\Filters\Concerns\HasFilters;
use App\Models\BoardGame\ShopItem;

class BgShopItemFilter
{
    use HasFilters;

    private const MODEL = ShopItem::class;
    private const TABLE_NAME = ShopItem::TABLE_NAME;
}
