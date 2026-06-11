<?php

namespace App\Models\BoardGame;

use App\Models\Traits\ExtendModelForBoardGameTrait;
use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlayerStatusEffect extends Model
{
    use HasFactory, ExtendModelTrait, ExtendModelForBoardGameTrait, SoftDeletes;

    public const CACHE_NAME = 'player-status-effects';
    public const TABLE_NAME = 'player_status_effects';

    public const CACHE_SERVICE = 'App\Services\Cache\BoardGame\StatusEffect\BgPlayerStatusEffectCacheService';
    public const FILTER = 'App\Filters\BoardGame\BgPlayerStatusEffectFilter';
    public const SERVICE = 'App\Services\BoardGame\PlayerStatusEffectService';

    /* Resource for admin panel */
    public const DETAIL_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgPlayerStatusEffect\DetailResource';
    public const LIST_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgPlayerStatusEffect\ListResource';

    /* Resource for public */
    public const PUBLIC_RESOURCES = [];

    protected $fillable = [
        'user_id', // TODO se_refactoring устаревшее
        'board_game_player_id',
        'board_game_id', // TODO se_refactoring устаревшее
        'status_effect_id', // TODO se_refactoring устаревшее
        'status_effect_bind_id',
        'active',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function statusEffect(): BelongsTo // TODO se_refactoring устаревшее
    {
        return $this->belongsTo(StatusEffect::class, 'status_effect_id');
    }

    public function statusEffectBind()
    {
        return $this->belongsTo(StatusEffectBind::class, 'status_effect_bind_id', 'id');
    }
}
