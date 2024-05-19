<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewsCount extends Model
{
    use HasFactory;

    protected $fillable = [
        'value',
        'entity_type',
        'entity_id',
    ];
}
