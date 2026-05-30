<?php

namespace App\Models\BoardGame;

use App\Models\Traits\ExtendModelForBoardGameTrait;
use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Timer extends Model
{
    use HasFactory, ExtendModelTrait, ExtendModelForBoardGameTrait, SoftDeletes;

    public const CACHE_NAME = 'timer';
    public const TABLE_NAME = 'timers';

    public const CACHE_SERVICE = 'App\Services\Cache\TimerCacheService';
    public const FILTER = 'App\Filters\TimerFilter';
    public const DETAIL_RESOURCE = 'App\Http\Resources\Admin\BoardGame\Timer\DetailResource';
    public const LIST_RESOURCE = 'App\Http\Resources\Admin\BoardGame\Timer\ListResource';
    public const SERVICE = 'App\Services\BoardGame\TimerService';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'limit',
        'settings',
        'active',
        'user_id',
        'board_game_id',
        'created_by',
    ];

    protected $casts = [
        'settings' => 'array',
        'active' => 'boolean',
    ];

    public function playerTimer()
    {
        return $this->hasMany(BoardGamePlayerTimer::class);
    }
}
