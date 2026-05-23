<?php

namespace App\Models;

use App\Models\Person\Person;
use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use HasFactory, ExtendModelTrait, SoftDeletes;

    public const CACHE_NAME = 'article';
    public const TABLE_NAME = 'articles';

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
        'entity_id',
        'entity_type',
        'created_by',
        'editor',
        'show_author',
        'show_editor',
        'sort',
        'active',
        'published_at',
        'created_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'show_author' => 'boolean',
        'show_editor' => 'boolean',
    ];

    public function getArticleTypeAttribute()
    {
        switch ($this->type) {
            case self::ARTICLE_TYPE: return 'article';
            case self::NEWS_TYPE: return 'news';
            case self::PROGRAM_TYPE: return 'program';
        }
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function articleEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor');
    }

    public function people()
    {
        return $this->morphToMany(Person::class, 'person_bind')->withTimestamps();
    }

    public function company()
    {
        return $this->morphToMany(Company::class, 'company_bind')->withPivot('additional_info')->withTimestamps();
    }

    public function link()
    {
        return $this->morphToMany(Link::class, 'link_bind')->withTimestamps();
    }
}
