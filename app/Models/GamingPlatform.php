<?php

namespace App\Models;

use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GamingPlatform extends Model
{
    use HasFactory, ExtendModelTrait;

    protected $fillable = [
        'name',
        'short_name',
        'slug',
        'description',
        'release_date',
        'sort',
    ];

}
