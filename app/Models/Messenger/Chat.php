<?php

namespace App\Models\Messenger;

use App\Models\Traits\ExtendModelTrait;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chat extends Model
{
    use HasFactory,
        ExtendModelTrait,
        SoftDeletes;

    protected $table = 'ms_chats';

    public const CACHE_NAME = 'bg-chat';
    public const TABLE_NAME = 'ms_chats';

    public const CACHE_SERVICE = 'App\Services\Cache\Messenger\ChatCacheService';
    public const FILTER = 'App\Filters\Messenger\ChatFilter';
    public const SERVICE = 'App\Services\Messenger\ChatService';

    public const OBSERVER = 'App\Observers\Messenger\ChatObserver';

    // Admin resource
    public const DETAIL_RESOURCE = 'App\Http\Resources\Admin\Messenger\Chat\DetailResource';
    public const LIST_RESOURCE = 'App\Http\Resources\Admin\Messenger\Chat\ListResource';

    protected $fillable = [
        'type',
        'title',
        'last_message_id',
        'last_message_at',
        'sort',
        'active',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'ms_chat_user')
            ->withPivot('role', 'last_read_message_id')->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function lastMessage()
    {
        return $this->belongsTo(Message::class, 'last_message_id');
    }

    // Скоуп для получения приватного чата между двумя пользователями
    public function scopePrivateBetween($query, $userId1, $userId2)
    {
        return $query->where('type', 'private')
            ->whereHas('users', fn($q) => $q->whereIn('user_id', [$userId1, $userId2]))
            ->whereDoesntHave('users', fn($q) => $q->whereNotIn('user_id', [$userId1, $userId2]));
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhereHas('users', fn($q) => $q->where('name', 'like', "%{$search}%"));
        });
    }

    public function getUnreadCountAttribute()
    {
        $currentUserId = auth()->id();
        $pivot = $this->users->firstWhere('id', $currentUserId);

        if (!$pivot) return 0;

        $lastReadId = $pivot->pivot->last_read_message_id ?? 0;

        return Message::where('chat_id', $this->id)
            ->where('user_id', '!=', $currentUserId) // Не считаем свои
            ->where('id', '>', $lastReadId)
            ->count();
    }
}
