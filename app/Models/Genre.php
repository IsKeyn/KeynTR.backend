<?php

namespace App\Models;

use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Genre extends Model
{
    use HasFactory, ExtendModelTrait, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
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
        return $this->morphedByMany(Game::class, 'genre_bind');
    }

    public function movies()
    {
        return $this->morphedByMany(Movie::class, 'genre_bind');
    }
}
