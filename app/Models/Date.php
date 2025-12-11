<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Date extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
    ];

    // Связки для сущности game
    public function gamePlatform()
    {
        return $this->morphToMany(GamingPlatform::class, 'gaming_platform_bind')->withPivot('additional_info');
    }

    public function games()
    {
        return $this->morphedByMany(Game::class, 'date_bind');
    }

    public function gamesAnons()
    {
        return $this->morphedByMany(Game::class, 'date_bind')->wherePivot('type', '=', Game::DATE_ANONS_TYPE);
    }

    // Связки для сущности GamingPlatform
    public function gamingPlatforms()
    {
        return $this->morphedByMany(GamingPlatform::class, 'date_bind');
    }
}
