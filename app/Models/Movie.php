<?php

namespace App\Models;

use App\Models\Person\Person;
use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Movie extends Model
{
    use HasFactory, ExtendModelTrait, SoftDeletes;

    public const CACHE_NAME = 'movie';
    public const TABLE_NAME = 'movies';

    const SERIES_TYPE = 2;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort',
        'active',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function dates()
    {
        return $this->morphToMany(Date::class, 'date_bind')->withPivot('type');
    }

    public function anonsDates()
    {
        return $this->morphToMany(Date::class, 'date_bind')
            ->withPivot('type')
            ->wherePivot('type', '=', Game::DATE_ANONS_TYPE)
            ->withTimestamps();
    }

    public function series()
    {
        return $this->morphToMany(Series::class, 'series_bind')->withTimestamps();
    }

    public function people()
    {
        return $this->morphToMany(Person::class, 'person_bind')->withTimestamps();
    }

    public function groups()
    {
        return $this->morphToMany(Group::class, 'group_bind')->withPivot('type')->wherePivot('type', '=', Game::SERIES_TYPE)->withTimestamps();
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
}
