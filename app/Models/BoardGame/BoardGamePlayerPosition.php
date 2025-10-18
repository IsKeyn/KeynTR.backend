<?php

namespace App\Models\BoardGame;

use App\Models\Traits\ExtendModelForBoardGameTrait;
use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardGamePlayerPosition extends Model
{
    use HasFactory, ExtendModelTrait, ExtendModelForBoardGameTrait;

    protected $fillable = [
        'user_id',
        'position',
        'board_game_id',
        'has_use_effect',
        'created_by',
    ];
}
