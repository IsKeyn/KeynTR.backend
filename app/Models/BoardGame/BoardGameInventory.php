<?php

namespace App\Models\BoardGame;

use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoardGameInventory extends Model
{
    use HasFactory, ExtendModelTrait;

    protected $fillable = [
        'user_id',
        'board_game_id',
        'board_game_item_id',
        'has_used',
        'created_by',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(ItemBind::class, 'board_game_item_id');
    }
}
