<?php

namespace App\Models\BoardGame;

use App\Models\Traits\ExtendModelForBoardGameTrait;
use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StatusEffect extends Model
{
    use HasFactory, ExtendModelTrait, ExtendModelForBoardGameTrait, SoftDeletes;

    public const CACHE_NAME = 'bg-status-effect';
    public const TABLE_NAME = 'status_effects';

    public const CACHE_SERVICE = 'App\Services\Cache\BoardGame\StatusEffect\BgStatusEffectCacheService';
    public const FILTER = 'App\Filters\BoardGame\BgStatusEffectFilter';
    public const SERVICE = 'App\Services\BoardGame\StatusEffectService';

    /* Resource for admin panel */
    public const DETAIL_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgStatusEffect\DetailResource';
    public const LIST_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgStatusEffect\ListResource';

    /* Resource for public */
    public const PUBLIC_RESOURCES = [];

    /* Type value */
    const DICE_TYPE = 0;
    const POINTS_TYPE = 1;
    const GAME_LIST_TYPE = 2;
    const OTHER = 10;

    const TYPES = [
        0 => ['name' => 'dices'],
        1 => ['name' => 'points'],
    ];

    protected $fillable = [
        'type',
        'name',
        'slug',
        'description',
        'actions',
        'board_game_id', // TODO se_refactoring устаревшее
        'debuff',
        'sort',
        'active',
    ];

    protected $casts = [
        'debuff' => 'boolean',
        'active' => 'boolean',
    ];

    public function statusEffectBinds(): HasMany
    {
        return $this->hasMany(StatusEffectBind::class, 'status_effect_id');
    }

    public function boardGames(): BelongsToMany
    {
        return $this->belongsToMany(
            BoardGame::class,
            'bg_status_effects_binds',
            'status_effect_id',
            'board_game_id'
        )->withPivot('active')
        ->withTimestamps();
    }
}
