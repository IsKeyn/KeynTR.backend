<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'code',
        'tags',
        'image',
        'text_preview',
        'text_full'
    ];

    public function getEntityAttribute() {
        //dd($this);
        // Пример возвращает $article->entity
    }

    public function comments()
    {
        return $this->morphMany(Comments::class, 'entity');
    }
}
