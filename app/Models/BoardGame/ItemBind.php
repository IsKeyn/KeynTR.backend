<?php

namespace App\Models\BoardGame;

use App\Models\Traits\ExtendModelForBoardGameTrait;
use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemBind extends Model
{
    protected $table = 'bg_items_binds';

    use HasFactory, ExtendModelTrait, ExtendModelForBoardGameTrait;

    protected $fillable = [
        'item_id',
        'board_game_id',
        'active',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
