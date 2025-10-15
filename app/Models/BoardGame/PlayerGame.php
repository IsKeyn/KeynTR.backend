<?php

namespace App\Models\BoardGame;

use App\Models\Comments;
use App\Models\User;
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
    const QUEUE = 4;

    protected $fillable = [
        'user_id',
        'board_game_game_list_id',
        'status',
        'board_game_id',
        'comment_id',
        'time',
        'created_by',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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
