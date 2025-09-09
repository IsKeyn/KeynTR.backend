<?php

namespace App\Models\BoardGame;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class BoardGamePlayer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'board_game_id',
        'points',
        'created_by',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Не будет работать, так как нет зависимости от игры
    public function inventory()
    {
        return $this->hasMany(BoardGameInventory::class, 'user_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }
}
