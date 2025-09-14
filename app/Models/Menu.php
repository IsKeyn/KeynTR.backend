<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

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
}
