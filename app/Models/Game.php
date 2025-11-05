<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class Game
 * @package App\Models
 * @property-read Game[]|array $fields
 */

class Game extends Model
{
    use HasFactory;

    const SERIES_TYPE = 1;

    const DATE_ANONS_TYPE = 1;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'active',
        'show_in_list',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'show_in_list' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function getModelAttribute()
    {
        return get_class($this);
    }

    public function platforms() // TODO где используется? В основном на сайте gamePlatform
    {
        return $this->morphToMany(GamingPlatform::class, 'gaming_platform_bind');
    }

    public function additionalFields()
    {
        return $this->morphMany(AdditionalField::class, 'entity');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'tag_binds');
    }

    public function media()
    {
        return $this->morphToMany(Media::class, 'media_bind')->withPivot('type');
    }

    public function titleImage()
    {
        return $this->morphToMany(Media::class, 'media_bind')->withPivot('type')->wherePivot('type', '=', Media::TITLE_TYPE);
    }

    public function cover()
    {
        return $this->morphToMany(Media::class, 'media_bind')->withPivot('type')->wherePivot('type', '=', Media::COVER_TYPE);
    }

    public function dates()
    {
        return $this->morphToMany(Date::class, 'date_bind')
            ->withPivot('type')
            ->wherePivot('type', '=', null)
            ->withTimestamps();
    }

    public function anonsDates()
    {
        return $this->morphToMany(Date::class, 'date_bind')
            ->withPivot('type')
            ->wherePivot('type', '=', Game::DATE_ANONS_TYPE)
            ->withTimestamps();
    }

    public function gamePlatform()
    {
        return $this->morphToMany(GamingPlatform::class, 'gaming_platform_bind')->withPivot('additional_info');
    }

    public function groups()
    {
        return $this->morphToMany(Group::class, 'group_bind')->withPivot('type')->wherePivot('type', '=', Game::SERIES_TYPE);
    }

    public function genres()
    {
        return $this->morphToMany(Genre::class, 'genre_bind');
    }

    public function company()
    {
        return $this->morphToMany(Company::class, 'company_bind')->withPivot('additional_info');
    }

    public function link()
    {
        return $this->morphToMany(Link::class, 'link_bind');
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

    public function menu()
    {
        return $this->morphMany(MenuType::class, 'menu_type_bind');
    }

    public function seo()
    {
        return $this->morphOne(Seo::class, 'entity');
    }

    public function blocks()
    {
        return $this->morphToMany(Block::class, 'block_bind')->withPivot('type')->orderBy('position', 'asc');
    }
}
