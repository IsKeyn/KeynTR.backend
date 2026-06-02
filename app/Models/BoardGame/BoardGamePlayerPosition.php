<?php

namespace App\Models\BoardGame;

use App\Models\Traits\ExtendModelForBoardGameTrait;
use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoardGamePlayerPosition extends Model
{
    use HasFactory, ExtendModelTrait, ExtendModelForBoardGameTrait, SoftDeletes;

    public const CACHE_NAME = 'bg-player-position';
    public const TABLE_NAME = 'board_game_player_positions';

    public const CACHE_SERVICE = 'App\Services\Cache\BoardGame\BgPlayerPositionCacheService';
    public const FILTER = 'App\Filters\BoardGame\BgPlayerPositionFilter';
    public const DETAIL_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgPlayerPosition\DetailResource';
    public const LIST_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgPlayerPosition\ListResource';
    public const SERVICE = 'App\Services\BoardGame\BgPlayerPositionService';

    protected $fillable = [
        'user_id',
        'position',
        'board_game_id',
        'has_use_effect',
        'sort',
        'active',
        'created_by',
    ];

    protected $casts = [
        'has_use_effect' => 'boolean',
        'active' => 'boolean',
    ];
}
