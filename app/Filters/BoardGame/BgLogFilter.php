<?php
namespace App\Filters\BoardGame;

use App\Filters\Concerns\HasFilters;
use App\Models\BoardGame\BoardGameLog;

class BgLogFilter
{
    use HasFilters;

    private const MODEL = BoardGameLog::class;
    private const TABLE_NAME = BoardGameLog::TABLE_NAME;
}
