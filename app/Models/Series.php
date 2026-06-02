<?php

namespace App\Models;

use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Series extends Model
{
    use HasFactory, ExtendModelTrait, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'sort',
        'active',
        'spc_id',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function games()
    {
        return $this->morphedByMany(Game::class, 'series_bind')->withTimestamps();
    }

    public function movies()
    {
        return $this->morphedByMany(Movie::class, 'series_bind');
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

    public function activeGamesExcept($excludeGameId = null)
    {
        return $this->game()
            ->where('active', true)
            ->when($excludeGameId, function ($query) use ($excludeGameId) {
                $id = is_object($excludeGameId) ? $excludeGameId->id : $excludeGameId;
                $query->where('id', '!=', $id);
            });
    }
}
