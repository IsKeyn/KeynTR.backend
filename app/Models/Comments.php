<?php

namespace App\Models;

use App\Models\BoardGame\PlayerGame;
use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comments extends Model
{
    use HasFactory, ExtendModelTrait;

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

    public function bgPlayerGame()
    {
        return $this->hasOne(PlayerGame::class, 'comment_id');
    }
}
