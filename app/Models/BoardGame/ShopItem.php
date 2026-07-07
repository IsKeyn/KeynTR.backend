<?php

namespace App\Models\BoardGame;

use App\Models\Traits\ExtendModelForBoardGameTrait;
use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopItem extends Model
{
    use HasFactory, ExtendModelTrait, ExtendModelForBoardGameTrait, SoftDeletes;

    protected $table = 'bg_shop_items';

    public const CACHE_NAME = 'bg-shop-item';
    public const TABLE_NAME = 'bg_shop_items';

    public const CACHE_SERVICE = 'App\Services\Cache\BoardGame\BgShopItemCacheService';
    public const FILTER = 'App\Filters\BoardGame\BgItemFilter';
    public const SERVICE = 'App\Services\BoardGame\ShopItemService';

    public const OBSERVER = 'App\Observers\BoardGame\BgShopItemObserver';

    public const ADMIN_CONTROLLER = 'App\Http\Controllers\Admin\BoardGame\BgShopItemController';
    public const REQUEST = 'App\Http\Requests\BoardGame\BgShopItemRequest';

    public const DETAIL_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgShopItem\DetailResource';
    public const LIST_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgShopItem\ListResource';

    public const STATUS_ON_SALE = 1;
    public const STATUS_WITHDRAWN = 2;
    public const STATUS_SOLD = 3;

    protected $fillable = [
        'bg_player_id',
        'user_id',
        'board_game_id',
        'entity_type',
        'entity_id',
        'status',
        'bought_by_player_id',
        'sort',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function entity()
    {
        return $this->morphTo();
    }

    public function seller()
    {
        return $this->belongsTo(BoardGamePlayer::class, 'bg_player_id');
    }

    public function buyer()
    {
        return $this->belongsTo(BoardGamePlayer::class, 'bought_by_player_id');
    }
}
