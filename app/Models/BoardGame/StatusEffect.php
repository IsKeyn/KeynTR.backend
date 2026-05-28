<?php

namespace App\Models\BoardGame;

use App\Models\Traits\ExtendModelForBoardGameTrait;
use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StatusEffect extends Model
{
    use HasFactory, ExtendModelTrait, ExtendModelForBoardGameTrait, SoftDeletes;

    public const CACHE_NAME = 'bg-status-effect';
    public const TABLE_NAME = 'status_effects';

    public const CACHE_SERVICE = 'App\Services\Cache\BoardGame\BgStatusEffectCacheService';
    public const FILTER = 'App\Filters\BoardGame\BgStatusEffectFilter';
    public const DETAIL_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgStatusEffect\DetailResource';
    public const LIST_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgStatusEffect\ListResource';
    public const SERVICE = 'App\Services\BoardGame\StatusEffectService';

    protected $fillable = [
        'type',
        'name',
        'slug',
        'description',
        'actions',
        'board_game_id',
        'debuff',
        'sort',
        'active',
    ];

    protected $casts = [
        'debuff' => 'boolean',
        'active' => 'boolean',
    ];

    const DICE_TYPE = 0;
    const POINTS_TYPE = 1;
    const GAME_LIST_TYPE = 2;
    const OTHER = 10;

    const TYPES = [
        0 => [
            'name' => 'dices'
        ],
        1 => [
            'name' => 'points'
        ],
    ];
}
