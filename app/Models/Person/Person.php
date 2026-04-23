<?php

namespace App\Models\Person;

use App\Models\Game;
use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    use HasFactory, ExtendModelTrait, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort',
        'active',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function game()
    {
        return $this->morphedByMany(Game::class, 'person_bind')->withTimestamps();
    }
}
