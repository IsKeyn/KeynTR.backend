<?php

namespace App\Models\Messenger;

use App\Models\Traits\ExtendModelTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attachment extends Model
{
    use HasFactory,
        ExtendModelTrait,
        SoftDeletes;

    protected $table = 'ms_message_attachments';

    public const CACHE_NAME = 'bg-message-attachment';
    public const TABLE_NAME = 'ms_message_attachments';

//    public const CACHE_SERVICE = 'App\Services\Cache\Messenger\MessageCacheService';
//    public const FILTER = 'App\Filters\Messenger\MessageFilter';
//    public const SERVICE = 'App\Services\Messenger\MessageService';
//
//    public const OBSERVER = 'App\Observers\Messenger\MessageObserver';

    // Admin resource
//    public const DETAIL_RESOURCE = 'App\Http\Resources\Admin\Messenger\Message\DetailResource';
//    public const LIST_RESOURCE = 'App\Http\Resources\Admin\Messenger\Message\ListResource';

    protected $fillable = [
        'message_id',
        'disk',
        'path',
        'mime_type',
        'size',
        'sort',
        'active',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
