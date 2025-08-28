<?php

namespace App\Models\BoardGame;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 'bg_items';

    use HasFactory;

    const TYPES = [
        0 => 'positive',
        1 => 'negative',
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'actions',
        'type',
        'board_game_id',
        'active',
        'author',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
//        'actions' => 'array',
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
