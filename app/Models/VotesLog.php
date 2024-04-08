<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VotesLog extends Model
{
    use HasFactory;

    public const LIKE = 1;
    public const DISLIKE = 2;
    public const RATING = 3;

    protected $fillable = [
        'vote_type',
        'vote_value',
        'entity_type',
        'entity_id',
    ];

    public function userAgentData()
    {
        return $this->morphMany(UserAgentData::class, 'entity');
    }
}
