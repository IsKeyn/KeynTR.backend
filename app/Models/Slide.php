<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slide extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'type',
        'active',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function getModelAttribute()
    {
        return get_class($this);
    }

    public function titleImage()
    {
        return $this->morphToMany(Media::class, 'media_bind')->withPivot('type');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'tag_binds');
    }

    public function media()
    {
        return $this->morphToMany(Media::class, 'media_bind');
    }
}
