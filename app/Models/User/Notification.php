<?php

namespace App\Models\User;

use App\Models\Traits\ExtendModelTrait;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, ExtendModelTrait, SoftDeletes;

    public const CACHE_NAME = 'notification';
    public const TABLE_NAME = 'notifications';

    protected $fillable = [
        'user_id',
        'message',
        'actions',
        'viewed',
        'entity_id',
        'entity_type',
        'sort',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'actions' => 'array',
        'viewed' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function entity()
    {
        return $this->morphTo();
    }
}
