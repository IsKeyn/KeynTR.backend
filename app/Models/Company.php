<?php

namespace App\Models;

use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory, ExtendModelTrait;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'release_date',
        'spc_id',
    ];

    public function game()
    {
        return $this->morphedByMany(Game::class, 'company_bind');
    }

    public function movie()
    {
        return $this->morphedByMany(Movie::class, 'company_bind');
    }

    public function group($id = null, $type = null)
    {
        return $this->morphToMany(Group::class, 'group_bind')
            ->withPivot(['first_b_id', 'first_b_type'])
            ->wherePivot('first_b_id', '=', $id)
            ->wherePivot('first_b_type', '=', $type)
            ->wherePivot('group_bind_id', '=', $this->id)
            ->withTimestamps();
    }
}
