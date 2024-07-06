<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'release_date',
    ];

    public function game()
    {
        return $this->morphedByMany(Game::class, 'company_bind');
    }

    public function group($id = null, $type = null)
    {
        return $this->morphToMany(Group::class, 'group_bind')
            ->withPivot(['first_b_id', 'first_b_type'])
            ->wherePivot('first_b_id', '=', $id)
            ->wherePivot('first_b_type', '=', $type)
            ->withTimestamps();
    }
}
