<?php

namespace App\Models;

use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    use HasFactory, ExtendModelTrait;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'spc_id',
    ];
}
