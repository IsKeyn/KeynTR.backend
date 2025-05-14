<?php

namespace App\Models\BoardGame;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusEffect extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'slug',
        'description',
        'actions',
        'board_game_id',
        'debuff',
    ];

    const DICE_TYPE = 0;
    const POINTS_TYPE = 1;

    const TYPES = [
        0 => [
            'name' => 'dices'
        ],
        1 => [
            'name' => 'points'
        ],
    ];
}
