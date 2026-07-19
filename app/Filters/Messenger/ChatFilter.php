<?php
namespace App\Filters\Messenger;

use App\Filters\Concerns\HasFilters;
use App\Models\Messenger\Chat;

class ChatFilter
{
    use HasFilters;

    private const MODEL = Chat::class;
    private const TABLE_NAME = Chat::TABLE_NAME;
}
