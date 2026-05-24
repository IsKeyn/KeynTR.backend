<?php

namespace App\Filters;

use App\Filters\Concerns\HasFilters;
use App\Models\BoardGame\BoardGamePlayerTimer;

class BgPlayerTimerFilter
{
    use HasFilters;

    private const MODEL = BoardGamePlayerTimer::class;
    private const TABLE_NAME = BoardGamePlayerTimer::TABLE_NAME;
}
