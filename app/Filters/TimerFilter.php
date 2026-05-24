<?php

namespace App\Filters;

use App\Filters\Concerns\HasFilters;
use App\Models\BoardGame\Timer;

class TimerFilter
{
    use HasFilters;

    private const MODEL = Timer::class;
    private const TABLE_NAME = Timer::TABLE_NAME;
}
