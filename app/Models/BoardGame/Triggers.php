<?php

namespace App\Models\BoardGame;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Triggers extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'slug',
        'description',
        'actions',
        'board_game_id',
    ];

    const TYPES = [
        0 => [
            'name' => 'dices'
        ],
        1 => [
            'name' => 'points'
        ],
    ];
}
