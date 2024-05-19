<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VotesCount extends Model
{
    use HasFactory;

    protected $fillable = [
        'vote_type',
        'value',
        'entity_type',
        'entity_id',
    ];
}
