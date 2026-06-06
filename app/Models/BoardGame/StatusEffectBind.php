<?php

namespace App\Models\BoardGame;

use App\Models\Traits\ExtendModelForBoardGameTrait;
use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StatusEffectBind extends Model
{
    use HasFactory, ExtendModelTrait, ExtendModelForBoardGameTrait, SoftDeletes;

    protected $table = 'bg_status_effects_binds';

    public const CACHE_NAME = 'bg-status-effect-binds';
    public const TABLE_NAME = 'bg_status_effects_binds';

    public const CACHE_SERVICE = 'App\Services\Cache\BoardGame\BgStatusEffectBindCacheService';
    public const FILTER = 'App\Filters\BoardGame\BgStatusEffectBindFilter';
    public const DETAIL_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgStatusEffectBind\DetailResource';
    public const LIST_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgStatusEffectBind\ListResource';
    public const SERVICE = 'App\Services\BoardGame\BgStatusEffectBindService';

    protected $fillable = [
        'status_effect_id',
        'board_game_id',
        'active',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function statusEffect(): BelongsTo
    {
        return $this->belongsTo(StatusEffect::class);
    }

    public function boardGame(): BelongsTo
    {
        return $this->belongsTo(BoardGame::class);
    }
}
