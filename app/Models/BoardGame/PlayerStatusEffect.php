<?php

namespace App\Models\BoardGame;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerStatusEffect extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'board_game_id',
        'status_effect_id',
        'active',
        'created_by',
    ];

    public function statusEffect(): BelongsTo
    {
        return $this->belongsTo(StatusEffect::class, 'status_effect_id');
    }

    public function titleImage()
    {
        return $this->morphToMany(Media::class, 'media_bind')->withPivot('type');
    }

    public function media()
    {
        return $this->morphToMany(Media::class, 'media_bind');
    }
}
