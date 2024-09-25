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

    public function gamePlatform()
    {
        return $this->morphToMany(GamingPlatform::class, 'gaming_platform_bind')->withPivot('additional_info');
    }

    public function games()
    {
        return $this->morphedByMany(Game::class, 'date_bind');
    }
}
