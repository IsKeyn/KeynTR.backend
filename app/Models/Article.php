<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    use HasFactory;

    const ARTICLE_TYPE = 0;
    const NEWS_TYPE = 1;
    const PROGRAM_TYPE = 2;

    protected $fillable = [
        'name',
        'slug', // TODO slug Должен быть уникальным, если entity_type пуст и также уникальным, при при одинаково заполненных entity_type и entity_id
        'text_preview',
        'text_full',
        'image',
        'type',
        'entity_id',
        'entity_type',
        'created_by',
        'editor',
        'show_author',
        'show_editor',
        'active',
        'published_at',
        'created_at',
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

    public function views()
    {
        return $this->morphOne(ViewsCount::class, 'entity');
    }

    public function likes()
    {
        return $this->morphOne(VotesCount::class, 'entity')->where('vote_type', VotesLog::LIKE);
    }

    public function blocks()
    {
        return $this->morphToMany(Block::class, 'block_bind')->withPivot('type')->orderBy('position', 'asc');
    }

    public function seo()
    {
        return $this->morphOne(Seo::class, 'entity');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function articleEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor');
    }
}
