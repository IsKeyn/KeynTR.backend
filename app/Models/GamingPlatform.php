<?php

namespace App\Models;

use App\Models\BoardGame\BoardGameGameList;
use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GamingPlatform extends Model
{
    use HasFactory, ExtendModelTrait, SoftDeletes;

    const REALISE_TYPE = 1;

    protected $fillable = [
        'name',
        'short_name',
        'slug',
        'description',
        'release_date',
        'spc_id',
        'sort',
        'active',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function realiseDates()
    {
        return $this->morphToMany(Date::class, 'date_bind')
            ->withPivot('type')
            ->wherePivot('type', '=', GamingPlatform::REALISE_TYPE)
            ->withTimestamps();
    }

    public function games()
    {
        return $this->morphedByMany(Game::class, 'gaming_platform_bind');
    }

    public function bgGamesList()
    {
        return $this->hasMany(BoardGameGameList::class, 'gaming_platform_id');
    }
}
