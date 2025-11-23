<?php

namespace App\Models\BoardGame;

use App\Models\Traits\ExtendModelForBoardGameTrait;
use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoardGameInventory extends Model
{
    use HasFactory, ExtendModelTrait, ExtendModelForBoardGameTrait;

    protected $fillable = [
        'user_id',
        'board_game_id',
        'board_game_item_id',
        'has_used',
        'use_result',
        'created_by',
    ];

    protected $casts = [
        'use_result' => 'array'
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(ItemBind::class, 'board_game_item_id');
    }
}
