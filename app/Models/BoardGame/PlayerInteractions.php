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

    use HasFactory, ExtendModelTrait, ExtendModelForBoardGameTrait;

    public const STATUS_ACTIVE = 1;
    public const STATUS_ACCEPTED = 2;
    public const STATUS_REFUSED = 3;
    public const I_WIN = 4;
    public const I_LOSE = 5;
    public const RECALLED = 6;

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
        'with_player',
        'created_by',
        'entity_id',
        'entity_type',
        'active',
    ];

    public function withPlayerData(): BelongsTo
    {
        return $this->belongsTo(User::class, 'with_player');
    }

    public function createdByData(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
