<?php

namespace App\Models\BoardGame;

use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardGamePlayerTimer extends Model
{
    use HasFactory, ExtendModelTrait;

    public const CACHE_NAME = 'BgPlayerTimer';
    public const TABLE_NAME = 'board_game_player_timers';

    protected $fillable = [
        'timer_id',
        'time_start',
        'time_stop',
        'created_by',
    ];

    public function timer()
    {
        return $this->belongsTo(Timer::class);
    }
}
