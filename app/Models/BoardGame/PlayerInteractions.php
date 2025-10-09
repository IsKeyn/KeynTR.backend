<?php

namespace App\Models\BoardGame;

use App\Models\Traits\ExtendModelTrait;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerInteractions extends Model
{
    protected $table = 'bg_player_interactions';

    use HasFactory, ExtendModelTrait;

    public const STATUS_ACTIVE = 1;
    public const STATUS_ACCEPTED = 2;
    public const STATUS_REFUSED = 3;

    protected $fillable = [
        'type',
        'status',
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
}
