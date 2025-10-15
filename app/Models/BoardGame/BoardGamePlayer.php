<?php

namespace App\Models\BoardGame;

use App\Models\Traits\ExtendModelTrait;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoardGamePlayer extends Model
{
    use HasFactory, ExtendModelTrait;

    protected $fillable = [
        'user_id',
        'board_game_id',
        'points',
        'item_roll_count',
        'step_count',
        'streak',
        'not_active_reason',
        'active',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function scopeFindByBoardGame($query, $boardGameId)
    {
        return $query->where('board_game_id', $boardGameId);
    }

    public function scopeFindByUserId($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function inventory()
    {
        return $this
            ->hasMany(BoardGameInventory::class, 'user_id', 'user_id')
            ->where('board_game_id', '=', $this->board_game_id);
    }

    public function statusEffects()
    {
        return $this
            ->hasMany(PlayerStatusEffect::class, 'user_id', 'user_id')
            ->where('board_game_id', '=', $this->board_game_id)
            ->active();
    }

    public function boardGame()
    {
        return $this->belongsTo(BoardGame::class, 'board_game_id');
    }
}
