<?php

namespace App\Models\BoardGame;

use App\Models\Traits\ExtendModelTrait;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, ExtendModelTrait, SoftDeletes;

    protected $table = 'bg_items';

    public const CACHE_NAME = 'bg-item';
    public const TABLE_NAME = 'bg_items';

    public const CACHE_SERVICE = 'App\Services\Cache\BoardGame\BgItemCacheService';
    public const FILTER = 'App\Filters\BoardGame\BgItemFilter';
    public const SERVICE = 'App\Services\BoardGame\ItemService';

    public const DETAIL_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgItem\DetailResource';
    public const LIST_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgItem\ListResource';

    const TYPES = [
        0 => 'positive',
        1 => 'negative',
    ];

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'full_description',
        'actions',
        'type',
        'drop_chance',
        'board_game_id',
        'active',
        'sort',
        'author',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'actions' => 'array',
        'active' => 'boolean',
    ];

    public function authorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author');
    }
}
