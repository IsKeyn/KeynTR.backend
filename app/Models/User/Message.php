<?php

namespace App\Models\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'message',
        'recipient',
        'viewed',
        'created_by',
        'created_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Связь с отправителем
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Связь с получателем
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient');
    }
}
