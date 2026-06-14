<?php

namespace App\Models\BoardGame;

use App\Models\Traits\ExtendModelForBoardGameTrait;
use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoardGameInventory extends Model
{
    use HasFactory, ExtendModelTrait, ExtendModelForBoardGameTrait, SoftDeletes;

    public const CACHE_NAME = 'bg-inventory';
    public const TABLE_NAME = 'board_game_inventories';

    public const CACHE_SERVICE = 'App\Services\Cache\BoardGame\BgInventoryCacheService';
    public const FILTER = 'App\Filters\BoardGame\BgInventoryFilter';
    public const SERVICE = 'App\Services\BoardGame\BgInventoryService';

    /* Resource for admin panel */
    public const DETAIL_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgInventory\DetailResource';
    public const LIST_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgInventory\ListResource';

    protected $fillable = [
        'user_id',
        'bg_player_id',
        'board_game_id',
        'board_game_item_id',
        'has_used',
        'use_result',
        'sort',
        'active',
        'created_by',
    ];

    protected $casts = [
        'use_result' => 'array',
        'active' => 'boolean',
        'has_used' => 'boolean',
    ];

    public function item(): BelongsTo // TODO может стоит переименовать в ItemBind по аналогии со StatusEffectBind
    {
        return $this->belongsTo(ItemBind::class, 'board_game_item_id');
    }

    public function player()
    {
        return $this->belongsTo(BoardGamePlayer::class, 'bg_player_id');
    }
}
