<?php
namespace App\Filters\BoardGame;

use App\Filters\Concerns\HasFilters;
use App\Models\BoardGame\BoardGamePlayerPosition;

class BgPlayerPositionFilter
{
    use HasFilters;

    private const MODEL = BoardGamePlayerPosition::class;
    private const TABLE_NAME = BoardGamePlayerPosition::TABLE_NAME;
}
