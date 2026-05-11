<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/*
 * field theme values
 * 0 - waterfall
 * 1 - simple
 */

class MediaGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'theme',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function mediaGroup()
    {
        return $this->morphToMany(Media::class, 'media_bind')->withPivot(['type', 'sort'])->wherePivot('type', '=', Media::MEDIA_GROUP);
    }

    public function page()
    {
        return $this->morphToMany(Page::class, 'page_bind');
    }
}
