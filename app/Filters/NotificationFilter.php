<?php

namespace App\Filters;

use App\Filters\Concerns\HasFilters;
use App\Models\User\Notification;

class NotificationFilter
{
    use HasFilters;

    private const MODEL = Notification::class;
    private const TABLE_NAME = Notification::TABLE_NAME;
}
