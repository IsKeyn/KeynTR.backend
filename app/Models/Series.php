<?php

namespace App\Models;

use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    use HasFactory, ExtendModelTrait;

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

    public function game()
    {
        return $this->morphedByMany(Game::class, 'series_bind');
    }
}
