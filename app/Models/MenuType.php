<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'group',
        'group_name',
        'group_icon',
        'menu_type_bind_id',
        'menu_type_bind_type',
        'sort',
        'active',
    ];

    protected $casts = [
        'group' => 'boolean',
        'active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeFindByCode($query, $code)
    {
        return $query->where('code', $code);
    }

    public function elements()
    {
        return $this->hasMany(Menu::class);
    }
}
