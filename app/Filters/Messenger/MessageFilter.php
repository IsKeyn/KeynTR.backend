<?php
namespace App\Filters\Messenger;

use App\Filters\Concerns\HasFilters;
use App\Models\Messenger\Message;

class MessageFilter
{
    use HasFilters;

    private const MODEL = Message::class;
    private const TABLE_NAME = Message::TABLE_NAME;
}
