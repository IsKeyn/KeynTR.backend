<?php

namespace App\Models\Game;

use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaveState extends Model
{
    use HasFactory, ExtendModelTrait, SoftDeletes;

    protected $fillable = [
        'player_id',
        'state',
        'entity_type',
        'entity_id',
        'sort',
        'active',
    ];

    protected $casts = [
        'state' => 'array',
        'active' => 'boolean',
    ];

    public function scopeFindByPlayer($query, $id)
    {
        return $query->where('player_id', $id);
    }

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }
}
