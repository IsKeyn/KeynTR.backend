<?php

namespace App\Models\BoardGame;

use App\Models\Traits\ExtendModelForBoardGameTrait;
use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoardPositionEffect extends Model
{
    use HasFactory, ExtendModelTrait, ExtendModelForBoardGameTrait, SoftDeletes;

    protected $table = 'bg_board_position_effects';

    public const CACHE_NAME = 'bg-board-position-effect';
    public const TABLE_NAME = 'bg_board_position_effects';

    public const CACHE_SERVICE = 'App\Services\Cache\BoardGame\BgBoardPositionEffectCacheService';
    public const FILTER = 'App\Filters\BoardGame\BgBoardPositionEffectFilter';
    public const DETAIL_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgBoardPositionEffect\DetailResource';
    public const LIST_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgBoardPositionEffect\ListResource';
    public const SERVICE = 'App\Services\BoardGame\BgBoardPositionEffectService';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'actions',
        'sort',
        'active',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function boardPositionEffectBinds()
    {
        return $this->hasMany(BoardPositionEffectsBind::class, 'position_effect_id');
    }
}
