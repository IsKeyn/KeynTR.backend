<?php

namespace App\Models\BoardGame;

use App\Models\Game;
use App\Models\GamingPlatform;
use App\Models\Traits\ExtendModelForBoardGameTrait;
use App\Models\Traits\ExtendModelTrait;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoardGameGameList extends Model
{
    use HasFactory, ExtendModelTrait, ExtendModelForBoardGameTrait, SoftDeletes;

    const GOLDEN_LIST = 1;

    public const CACHE_NAME = 'bg-game-list';
    public const TABLE_NAME = 'board_game_game_lists';

    public const CACHE_SERVICE = 'App\Services\Cache\BoardGame\BgGameListCacheService';
    public const FILTER = 'App\Filters\BoardGame\BgGameListFilter';
    public const SERVICE = 'App\Services\BoardGame\BgGameListService';

    public const OBSERVER = 'App\Observers\BoardGame\BgGameListObserver';

    public const DETAIL_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgGameList\DetailResource';
    public const LIST_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgGameList\ListResource';

    protected $fillable = [
        'game_id',
        'board_game_id',
        'gaming_platform_id',
        'points',
        'difficult',
        'game_completion_time',
        'coop',
        'list_type',
        'description',
        'sort',
        'active',
        'source',
        'added_by',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
        'coop' => 'boolean',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(GamingPlatform::class, 'gaming_platform_id');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function playerGames(): HasMany
    {
        return $this->hasMany(PlayerGame::class, 'board_game_game_list_id');
    }
}
