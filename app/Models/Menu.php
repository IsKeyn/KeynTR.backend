<?php

namespace App\Models;

use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use HasFactory, ExtendModelTrait, SoftDeletes;

    protected $fillable = [
        'name',
        'url',
        'target',
        'menu_type_id',
        'link_type',
        'icon',
        'sort',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function type()
    {
        return $this->belongsTo(MenuType::class);
    }

    public function permissions() // Разрешения, необходимы для отображения элемента меню
    {
        return $this->morphToMany(Permission::class, 'permission_bind')->withTimestamps();
    }
}
