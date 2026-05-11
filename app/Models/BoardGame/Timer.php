<?php

namespace App\Models\BoardGame;

use App\Models\Traits\ExtendModelForBoardGameTrait;
use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timer extends Model
{
    use HasFactory, ExtendModelTrait, ExtendModelForBoardGameTrait;

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

    protected $casts = [
        'active' => 'boolean',
    ];

    public function playerTimer()
    {
        return $this->hasMany(BoardGamePlayerTimer::class);
    }
}
