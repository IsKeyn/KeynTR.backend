<?php

namespace App\Models\BoardGame;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardGameItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'board_game_id',
        'active',
        'created_by',
    ];

    public function titleImage()
    {
        return $this->morphToMany(Media::class, 'media_bind')->withPivot('type');
    }

    public function media()
    {
        return $this->morphToMany(Media::class, 'media_bind');
    }
}
