<?php

namespace App\Models\BoardGame;

use App\Models\Media;
use App\Models\Traits\ExtendModelTrait;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
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
    public const DETAIL_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgItem\DetailResource';
    public const LIST_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgItem\ListResource';
    public const SERVICE = 'App\Services\BoardGame\ItemService';

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
        'active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function titleImage()
    {
        return $this->morphToMany(Media::class, 'media_bind')->withPivot('type');
    }

    public function media()
    {
        return $this->morphToMany(Media::class, 'media_bind');
    }

    public function authorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author');
    }
}
