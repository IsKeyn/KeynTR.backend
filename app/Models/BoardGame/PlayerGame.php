<?php

namespace App\Models\BoardGame;

use App\Models\Comments;
use App\Models\Traits\ExtendModelForBoardGameTrait;
use App\Models\Traits\ExtendModelTrait;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlayerGame extends Model
{
    use HasFactory, ExtendModelTrait, ExtendModelForBoardGameTrait, SoftDeletes;

    public const CACHE_NAME = 'bg-player-game';
    public const TABLE_NAME = 'player_games';

    public const CACHE_SERVICE = 'App\Services\Cache\BoardGame\BgPlayerGameCacheService';
    public const FILTER = 'App\Filters\BoardGame\BgPlayerGameFilter';
    public const SERVICE = 'App\Services\BoardGame\BgPlayerGameService';

    /* Resource for admin panel */
    public const DETAIL_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgPlayerGame\DetailResource';
    public const LIST_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgPlayerGame\ListResource';

    /* Статусы */
    // TODO в будущем добавить префикс STATUS_ , пример STATUS_CURRENT
    const CURRENT = 0;
    const REROLLED = 1;
    const COMPLETED = 2;
    const GIVEN_AWAY = 3;
    const QUEUE = 4;

    /* Типы игры */
    const TYPE_TAKEN = 0;
    const TYPE_PURSE = 1;

    protected $fillable = [
        'user_id',
        'bg_player_id',
        'board_game_game_list_id',
        'status',
        'board_game_id',
        'type',
        'from_user_id',
        'comment_id',
        'time',
        'points',
        'sort',
        'active',
        'created_by',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function player()
    {
        return $this->belongsTo(BoardGamePlayer::class, 'bg_player_id');
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(BoardGameGameList::class, 'board_game_game_list_id');
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comments::class, 'comment_id');
    }
}
