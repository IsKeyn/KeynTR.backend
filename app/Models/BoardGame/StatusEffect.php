<?php

namespace App\Models\BoardGame;

use App\Models\Traits\ExtendModelForBoardGameTrait;
use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusEffect extends Model
{
    use HasFactory, ExtendModelTrait, ExtendModelForBoardGameTrait;

    protected $fillable = [
        'type',
        'name',
        'slug',
        'description',
        'actions',
        'board_game_id',
        'debuff',
    ];

    protected $casts = [
        'debuff' => 'boolean',
    ];

    const DICE_TYPE = 0;
    const POINTS_TYPE = 1;
    const GAME_LIST_TYPE = 2;

    const TYPES = [
        0 => [
            'name' => 'dices'
        ],
        1 => [
            'name' => 'points'
        ],
    ];
}
