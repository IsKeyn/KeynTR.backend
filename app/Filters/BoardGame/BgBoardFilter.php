<?php
namespace App\Filters\BoardGame;

use App\Filters\Concerns\HasFilters;
use App\Models\BoardGame\Board;

class BgBoardFilter
{
    use HasFilters;

    private const MODEL = Board::class;
    private const TABLE_NAME = Board::TABLE_NAME;
}
