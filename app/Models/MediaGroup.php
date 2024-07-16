<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'active'
    ];

    public function mediaGroup()
    {
        return $this->morphToMany(Media::class, 'media_bind')->withPivot('type')->wherePivot('type', '=', Media::MEDIA_GROUP);
    }
}
