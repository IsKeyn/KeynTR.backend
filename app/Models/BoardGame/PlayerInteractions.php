<?php

namespace App\Models\BoardGame;

use App\Models\Traits\ExtendModelForBoardGameTrait;
use App\Models\Traits\ExtendModelTrait;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerInteractions extends Model
{
    protected $table = 'bg_player_interactions';

    use HasFactory,
        ExtendModelTrait,
        ExtendModelForBoardGameTrait;

    public const CACHE_NAME = 'bg-player-interaction';
    public const TABLE_NAME = 'bg_players_interactions';

    public const CACHE_SERVICE = 'App\Services\Cache\BoardGame\BgPlayerInteractionCacheService';
    public const FILTER = 'App\Filters\BoardGame\BgPlayerInteractionFilter';
    public const SERVICE = 'App\Services\BoardGame\InteractionsService';

    public const ADMIN_CONTROLLER = 'App\Http\Controllers\Admin\BoardGame\BgPlayerInteractionController';

    public const OBSERVER = 'App\Observers\BoardGame\BgPlayerInteractionsObserver';

    /* Resource for admin panel */
    public const DETAIL_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgPlayerInteraction\DetailResource';
    public const LIST_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgPlayerInteraction\ListResource';

    /* Resource for public */
    public const PUBLIC_RESOURCES = [];

    public const STATUS_ACTIVE = 1;
    public const STATUS_ACCEPTED = 2;
    public const STATUS_REFUSED = 3;
    public const I_WIN = 4;
    public const I_LOSE = 5;
    public const RECALLED = 6;
    public const COOP_FINISH = 7;

    public const TYPE_NAME = [
      'ru' => [
          'switchGame' => 'Обмен игрой',
          'battleForPoints' => 'Битва за очки',
          'inviteToCoop' => 'Приглашение в кооп',
          'playForMe' => 'Пройди игру за меня',
      ],
      'en' => [
          'switchGame' => 'Switch game',
          'battleForPoints' => 'Battle for points',
          'inviteToCoop' => 'Invite to coop',
          'playForMe' => 'Play for me',
      ],
    ];

    protected $fillable = [
        'type',
        'status',
        'description',
        'board_game_id',
        'bg_player_id',
        'with_player',
        'created_by',
        'entity_id',
        'entity_type',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function withPlayerData(): BelongsTo
    {
        return $this->belongsTo(User::class, 'with_player');
    }

    public function createdByData(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function player()
    {
        return $this->belongsTo(BoardGamePlayer::class, 'bg_player_id');
    }

    public function entity()
    {
        return $this->morphTo();
    }
}
