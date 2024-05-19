<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    const ARTICLE_TYPE = 0;
    const NEWS_TYPE = 1;
    const PROGRAM_TYPE = 2;

    protected $fillable = [
        'name',
        'slug',
        'text_preview',
        'text_full',
        'image',
        'type',
    ];

    public function getModelAttribute()
    {
        return get_class($this);
    }

    public function getArticleTypeAttribute()
    {
        switch ($this->type) {
            case self::ARTICLE_TYPE: return 'article';
            case self::NEWS_TYPE: return 'news';
            case self::PROGRAM_TYPE: return 'program';
        }
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'tag_binds');
    }

    public function comments()
    {
        return $this->morphMany(Comments::class, 'entity');
    }

    public function media()
    {
        return $this->morphToMany(Media::class, 'media_bind');
    }

    public function titleImage()
    {
        return $this->morphToMany(Media::class, 'media_bind')->withPivot('type');
    }

    public function likes()
    {
        return $this->morphOne(VotesCount::class, 'entity')->where('vote_type', VotesLog::LIKE);
    }
}
