<?php
namespace App\Filters\BoardGame;

use App\Filters\Concerns\HasFilters;
use App\Models\BoardGame\BoardGame;

class BgPlayerFilter
{
    use HasFilters;

    private const MODEL = BoardGame::class;
    private const TABLE_NAME = BoardGame::TABLE_NAME;
}
