<?php

namespace App\Models\BoardGame;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardGame extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'active',
        'created_by',
    ];

    public function getModelAttribute()
    {
        return get_class($this);
    }

    public function titleImage()
    {
        return $this->morphToMany(Media::class, 'media_bind')->withPivot('type');
    }

    public function media()
    {
        return $this->morphToMany(Media::class, 'media_bind');
    }

    public function players()
    {
        return $this->hasMany(BoardGamePlayer::class, 'board_game_id');
    }

}
