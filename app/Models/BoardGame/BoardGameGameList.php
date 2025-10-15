<?php

namespace App\Models\BoardGame;

use App\Models\Game;
use App\Models\GamingPlatform;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoardGameGameList extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'board_game_id',
        'gaming_platform_id',
        'points',
        'difficult',
        'game_completion_time',
        'description',
        'active',
        'added_by',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(GamingPlatform::class, 'gaming_platform_id');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
