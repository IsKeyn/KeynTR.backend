<?php

namespace App\Models\BoardGame;

use App\Services\BoardGame\BoardService;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ExtendModelTrait;
use App\Models\Traits\ExtendModelForBoardGameTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class BoardGamePlayer extends Model
{
    use HasFactory, ExtendModelTrait, ExtendModelForBoardGameTrait;

    protected $fillable = [
        'user_id',
        'board_game_id',
        'points',
        'item_roll_count',
        'step_count',
        'streak',
        'rerolled_own_game_count',
        'not_active_reason',
        'active',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function inventory()
    {
        return $this
            ->hasMany(BoardGameInventory::class, 'user_id', 'user_id');
    }

    public function statusEffects()
    {
        return $this
            ->hasMany(PlayerStatusEffect::class, 'user_id', 'user_id')
            ->active();
    }

    public function positions()
    {
        return $this
            ->hasMany(BoardGamePlayerPosition::class, 'user_id', 'user_id');
    }

    public function current_game()
    {
        return $this
            ->hasMany(PlayerGame::class, 'user_id', 'user_id')
            ->where('status', PlayerGame::CURRENT);
    }

    public function mainTimers()
    {
        return $this
            ->hasMany(Timer::class, 'user_id', 'user_id')
            ->findBySlug('main')
            ->active();
    }

    public function getFinishBoardAttribute()
    {
        $boardGame = \App\Models\BoardGame\BoardGame::where('id', $this->board_game_id)->first();

        return BoardService::getMaxBoardPosition($boardGame) === $this->positions->sortByDesc('id')->first()->position;
    }

    public function getPositionAttribute()
    {
        return $this
            ->positions
            ->where('board_game_id', $this->board_game_id)->sortByDesc('id')->first()->position;
    }
}
