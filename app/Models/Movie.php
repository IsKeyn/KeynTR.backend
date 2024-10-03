<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'created_by',
        'created_at',
    ];

    public function getModelAttribute()
    {
        return get_class($this);
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
        return $this->morphToMany(Date::class, 'date_bind')->withPivot('type');
    }

    public function genres()
    {
        return $this->morphToMany(Genre::class, 'genre_bind');
    }

    public function company()
    {
        return $this->morphToMany(Company::class, 'company_bind');
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
}
