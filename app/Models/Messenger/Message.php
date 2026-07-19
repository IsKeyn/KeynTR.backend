<?php

namespace App\Models\Messenger;

use App\Models\Traits\ExtendModelTrait;
use App\Models\User;
use App\Services\MessageCipher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory,
        ExtendModelTrait,
        SoftDeletes;

    protected $table = 'ms_messages';

    public const CACHE_NAME = 'bg-message';
    public const TABLE_NAME = 'ms_messages';

    public const CACHE_SERVICE = 'App\Services\Cache\Messenger\MessageCacheService';
    public const FILTER = 'App\Filters\Messenger\MessageFilter';
    public const SERVICE = 'App\Services\Messenger\MessageService';

    public const OBSERVER = 'App\Observers\Messenger\MessageObserver';

    // Admin resource
    public const DETAIL_RESOURCE = 'App\Http\Resources\Admin\Messenger\Message\DetailResource';
    public const LIST_RESOURCE = 'App\Http\Resources\Admin\Messenger\Message\ListResource';

    protected $fillable = [
        'chat_id',
        'user_id',
        'reply_to_id',
        'type',
        'body',
        'sort',
        'active',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Статический экземпляр шифровщика, чтобы не создавать объект каждый раз
    private static ?MessageCipher $cipher = null;

    private static function getCipher(): MessageCipher
    {
        if (!self::$cipher) {
            self::$cipher = new MessageCipher();
        }
        return self::$cipher;
    }

    /**
     * Мутиратор: шифрует при записи в БД
     */
    public function setBodyAttribute($value)
    {
        if ($value === null) {
            $this->attributes['body'] = null;
            return;
        }
        $this->attributes['body'] = self::getCipher()->encrypt($value);
    }

    /**
     * Акцессор: дешифрует при чтении из БД
     */
    public function getBodyAttribute($value)
    {
        if ($value === null) {
            return null;
        }
        return self::getCipher()->decrypt($value);
    }

    // Отправитель сообщения
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    // Чат, к которому принадлежит сообщение
    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    // Сообщение, на которое отвечают
    public function replyTo()
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    // Ответы на это сообщение
    public function replies()
    {
        return $this->hasMany(Message::class, 'reply_to_id');
    }
}
