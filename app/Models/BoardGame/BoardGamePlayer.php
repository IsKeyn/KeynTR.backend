<?php

namespace App\Models\BoardGame;

use App\Services\BoardGame\BoardService;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ExtendModelTrait;
use App\Models\Traits\ExtendModelForBoardGameTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoardGamePlayer extends Model
{
    use HasFactory, ExtendModelTrait, ExtendModelForBoardGameTrait, SoftDeletes;

    public const CACHE_NAME = 'bg-player';
    public const TABLE_NAME = 'board_game_players';

    public const CACHE_SERVICE = 'App\Services\Cache\BoardGame\BgPlayerCacheService';
    public const FILTER = 'App\Filters\BoardGame\BgPlayerFilter';
    public const SERVICE = 'App\Services\BoardGame\BgPlayerService';
    public const OBSERVER = 'App\Observers\BoardGame\BgPlayerObserver';

    public const ADMIN_CONTROLLER = 'App\Http\Controllers\Admin\BoardGame\BgPlayerController';
    public const REQUEST = 'App\Http\Requests\BoardGame\BgPlayerRequest';

    public const PUBLIC_CONTROLLER = 'App\Http\Controllers\BoardGame\PlayerGameController';

    // Resource for admin panel
    public const DETAIL_RESOURCE = 'App\Http\Resources\Admin\BoardGame\Player\DetailResource';
    public const LIST_RESOURCE = 'App\Http\Resources\Admin\BoardGame\Player\ListResource';

    // Resource for public
    public const PUBLIC_RESOURCES = [];

    public const MEDIA_BG_IMAGE = 1;

    protected $fillable = [
        'user_id',
        'board_game_id',
        'points',
        'points_per_hour',
        'place',
        'item_roll_count',
        'step_count',
        'streak',
        'rerolled_game_count',
        'rerolled_own_game_count',
        'active',
        'not_active_reason',
        'added_games',
        'settings',
        'premium',
        'sort',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
        'added_games' => 'boolean',
        'premium' => 'boolean',
        'settings' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this
            ->belongsTo(User::class, 'user_id');
    }

//    public function inventory()
//    {
//        return $this
//            ->hasMany(BoardGameInventory::class, 'user_id', 'user_id');
//    }

    public function inventory()
    {
        return $this->hasMany(BoardGameInventory::class, 'bg_player_id');
    }

//    public function statusEffects() // TODO устаревший se_refactoring
//    {
//        return $this
//            ->hasMany(PlayerStatusEffect::class, 'user_id', 'user_id')
//            ->active();
//    }

    public function statusEffects()
    {
        return $this->hasMany(PlayerStatusEffect::class, 'bg_player_id');
    }

    public function positions()
    {
        // TODO переделать на return $this->hasMany(BoardGamePlayerPosition::class, 'bg_player_id');
        return $this->hasMany(BoardGamePlayerPosition::class, 'user_id', 'user_id');
    }

    public function playerPositions()
    {
        return $this->hasMany(BoardGamePlayerPosition::class, 'bg_player_id');
    }

//    public function currentGames()
//    {
//        return $this
//            ->hasMany(PlayerGame::class, 'user_id', 'user_id')
//            ->where('status', PlayerGame::CURRENT);
//    }

    public function currentGames()
    {
        return $this
            ->hasMany(PlayerGame::class, 'bg_player_id')
            ->where('status', PlayerGame::CURRENT);
    }

    public function games()
    {
        return $this->hasMany(PlayerGame::class, 'bg_player_id');
    }

    public function mainTimers()
    {
        return $this
            ->hasMany(Timer::class, 'user_id', 'user_id')
            ->findBySlug('main')
            ->active();
    }

    public function getFinishBoardAttribute()
    {
        $boardGame = $this->relationLoaded('boardGame')
            ? $this->boardGame
            : BoardGame::find($this->board_game_id);

        if (!$boardGame) return false;

        $lastPos = $this->positions->sortByDesc('id')->first();

        $currentPosition = optional($lastPos)->position;

        return BoardService::getMaxBoardPosition($boardGame) === $currentPosition;
    }

    public function getPositionAttribute()
    {
        return $this->positions
            ->where('board_game_id', $this->board_game_id)
            ->sortByDesc('id')
            ->first()?->position;
    }

    public function userInteraction()
    {
        return $this->hasMany(PlayerInteractions::class, 'created_by', 'user_id');
    }

    public function playerInteractions()
    {
        return $this->hasMany(PlayerInteractions::class, 'bg_player_id');
    }
}
