<?php

namespace App\Models\BoardGame;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardPositionEffectBind extends Model
{
    protected $table = 'bg_board_position_effect_binds';

    use HasFactory;

    protected $fillable = [
        'position_effect_id',
        'board_game_id',
        'position',
        'active',
        'created_by',
    ];
}
