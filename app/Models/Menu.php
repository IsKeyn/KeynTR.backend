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
        'menu_type_id',
        'link_type',
    ];

    public function type()
    {
        return $this->belongsTo(MenuType::class);
    }
}
