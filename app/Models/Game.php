<?php

namespace App\Models;

use App\Models\Person\Person;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ExtendModelTrait;
use App\Models\BoardGame\BoardGameGameList;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Game
 * @package App\Models
 * @property-read Game[]|array $fields
 */

class Game extends Model
{
    use HasFactory, ExtendModelTrait, SoftDeletes;

    const SERIES_TYPE = 1;

    const DATE_ANONS_TYPE = 1;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'mod',
        'active',
        'show_in_list',
        'spc_id',
        'sort',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'show_in_list' => 'boolean',
        'mod' => 'boolean',
    ];

    public function cover()
    {
        return $this->morphToMany(Media::class, 'media_bind')->withPivot('type', 'sort')->wherePivot('type', '=', Media::COVER_TYPE)->withTimestamps();
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
        return $this->morphToMany(GamingPlatform::class, 'gaming_platform_bind')->withPivot('additional_info')->withTimestamps();
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
        return $this->morphToMany(Genre::class, 'genre_bind')->withTimestamps();
    }

    public function company()
    {
        return $this->morphToMany(Company::class, 'company_bind')->withPivot('additional_info')->withTimestamps();
    }

    public function link()
    {
        return $this->morphToMany(Link::class, 'link_bind')->withTimestamps();
    }

    public function bgGamesList()
    {
        return $this->hasMany(BoardGameGameList::class, 'game_id')->where('active', true);
    }
}
