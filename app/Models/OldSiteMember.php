<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OldSiteMember extends Model
{
    use HasFactory;

    const FROM_COMMENTS = 1;
    const FROM_QUIZ = 2;
    const FROM_FORUM = 3;

    protected $fillable = [
        'names',
        'email',
        'type',
        'was_handled',
        'first_message_date',
        'message_count',
        'score_count',
        'score_percent',
        'best_of',
        'created_at',
    ];

    protected $casts = [
        'names' => 'array'
    ];
}
