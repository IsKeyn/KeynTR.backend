<?php
namespace App\Filters\BoardGame;

use App\Filters\Concerns\HasFilters;
use App\Models\BoardGame\BoardGameGameList;

class BgGameListFilter
{
    use HasFilters;

    private const MODEL = BoardGameGameList::class;
    private const TABLE_NAME = BoardGameGameList::TABLE_NAME;
}
