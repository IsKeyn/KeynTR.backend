<?php

namespace App\Models\BoardGame;

use App\Models\Traits\ExtendModelForBoardGameTrait;
use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoardPositionEffectsBind extends Model
{
    use HasFactory, ExtendModelTrait, ExtendModelForBoardGameTrait, SoftDeletes;

    protected $table = 'bg_board_position_effect_binds';

    public const CACHE_NAME = 'bg-board-position-effect';
    public const TABLE_NAME = 'bg_board_position_effect_binds';

    public const CACHE_SERVICE = 'App\Services\Cache\BoardGame\BgBoardPositionEffectBindCacheService';
    public const FILTER = 'App\Filters\BoardGame\BgBoardPositionEffectBindFilter';
    public const DETAIL_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgBoardPositionEffectBind\DetailResource';
    public const LIST_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgBoardPositionEffectBind\ListResource';
    public const SERVICE = 'App\Services\BoardGame\BgBoardPositionEffectBindService';

    protected $fillable = [
        'position_effect_id',
        'board_game_id',
        'position',
        'active',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function boardPositionEffect()
    {
        return $this->belongsTo(BoardPositionEffect::class, 'position_effect_id');
    }
}
