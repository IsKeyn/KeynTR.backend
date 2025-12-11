<?php

namespace App\Models;

use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GamingPlatform extends Model
{
    use HasFactory, ExtendModelTrait;

    const REALISE_TYPE = 1;

    protected $fillable = [
        'name',
        'short_name',
        'slug',
        'description',
        'release_date',
        'spc_id',
        'sort',
    ];

    public function realiseDates()
    {
        return $this->morphToMany(Date::class, 'date_bind')
            ->withPivot('type')
            ->wherePivot('type', '=', GamingPlatform::REALISE_TYPE)
            ->withTimestamps();
    }
}
