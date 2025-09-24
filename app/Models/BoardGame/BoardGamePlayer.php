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
        'not_active_reason',
        'active',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function scopeFindByUserId($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Не будет работать, так как нет зависимости от игры
    public function inventory()
    {
        return $this->hasMany(BoardGameInventory::class, 'user_id');
    }

    public function boardGame()
    {
        return $this->belongsTo(BoardGame::class, 'board_game_id');
    }
}
