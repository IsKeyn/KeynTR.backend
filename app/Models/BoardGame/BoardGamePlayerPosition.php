<?php

namespace App\Models\BoardGame;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardGamePlayerPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'position',
        'board_game_id',
        'created_by',
    ];
}
