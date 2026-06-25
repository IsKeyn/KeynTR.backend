<?php

namespace App\Models\BoardGame;

use App\Models\Traits\ExtendModelForBoardGameTrait;
use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemBind extends Model
{
    use HasFactory, ExtendModelTrait, ExtendModelForBoardGameTrait;

    protected $table = 'bg_items_binds';

    public const CACHE_NAME = 'bg-item-bind';
    public const TABLE_NAME = 'bg_items_binds';

    public const CACHE_SERVICE = 'App\Services\Cache\BoardGame\BgItemBindCacheService';
    public const FILTER = 'App\Filters\BoardGame\BgItemBindFilter';
    public const DETAIL_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgItemBind\DetailResource';
    public const LIST_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgItemBind\ListResource';
    public const SERVICE = 'App\Services\BoardGame\BgItemBindService';

    protected $fillable = [
        'item_id',
        'board_game_id',
        'active',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(BoardGameInventory::class, 'board_game_item_id');
    }
}
