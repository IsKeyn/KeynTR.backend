<?php

namespace App\Models;

use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory, ExtendModelTrait;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'entity_type',
    ];
}
