<?php

namespace App\Models\BoardGame;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'limit',
        'active',
        'user_id',
        'board_game_id',
        'created_by',
    ];

    public function playerTimer()
    {
        return $this->hasMany(BoardGamePlayerTimer::class);
    }
}
