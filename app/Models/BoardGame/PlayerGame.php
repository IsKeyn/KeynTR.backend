<?php

namespace App\Models\BoardGame;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerGame extends Model
{
    use HasFactory;

    const CURRENT = 0;
    const REROLLED = 1;
    const COMPLETED = 2;
    const GIVEN_AWAY = 3;

    protected $fillable = [
        'user_id',
        'board_game_game_list_id',
        'status',
        'board_game_id',
        'comment_id',
        'time',
        'created_by',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(BoardGameGameList::class, 'board_game_game_list_id');
    }
}
