<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'group',
        'menu_type_bind_id',
        'menu_type_bind_type',
    ];

    public function elements()
    {
        return $this->hasMany(Menu::class);
    }
}
