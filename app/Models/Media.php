<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    const TITLE_TYPE = 1;
    const COVER_TYPE = 2;

    protected $fillable = [
        'name',
        'description',
        'type',
        'file_name',
        'mime_type',
        'size',
        'created_by',
    ];

    public function getModelAttribute()
    {
        return get_class($this);
    }

    public function getUrlAttribute()
    {
        return config('app.url') . '/storage/media/' . $this->id . '/' . $this->file_name;
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'tag_binds');
    }

    public function comments()
    {
        return $this->morphMany(Comments::class, 'entity');
    }

    public function views()
    {
        return $this->morphOne(ViewsCount::class, 'entity');
    }

    public function likes()
    {
        return $this->morphOne(VotesCount::class, 'entity')->where('vote_type', VotesLog::LIKE);
    }
}
