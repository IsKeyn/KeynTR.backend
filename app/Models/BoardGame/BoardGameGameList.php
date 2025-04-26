<?php

namespace App\Models\BoardGame;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardGameGameList extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'board_game_id',
        'created_by',
    ];
}
