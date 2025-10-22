<?php

namespace App\Models\BoardGame;

use App\Models\Traits\ExtendModelForBoardGameTrait;
use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardPositionEffectsBind extends Model
{
    protected $table = 'bg_board_position_effect_binds';

    use HasFactory, ExtendModelTrait, ExtendModelForBoardGameTrait;

    protected $fillable = [
        'position_effect_id',
        'board_game_id',
        'position',
        'active',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function boardPositionEffect()
    {
        return $this->belongsTo(BoardPositionEffect::class, 'position_effect_id');
    }
}
