<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comments extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'url', // Поле пришедшее из WordPress, хранит url (сайт пользователя), оставленный при отправке комментария
        'message',
        'first_parent',
        'answer_to',
        'entity_type',
        'entity_id',
        'created_by',
        'created_at_gmt',
        'created_at',
        'active',
    ];

    public function entity()
    {
        return $this->morphTo();
    }

    public function userAgentData()
    {
        return $this->morphMany(UserAgentData::class, 'entity');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
