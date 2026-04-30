<?php

namespace App\Models;

use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, ExtendModelTrait, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort',
        'active',
        'release_date',
        'spc_id',
        'created_by',
    ];

    public function game()
    {
        return $this->morphedByMany(Game::class, 'company_bind');
    }

    public function movie()
    {
        return $this->morphedByMany(Movie::class, 'company_bind');
    }
}
