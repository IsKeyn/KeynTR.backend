<?php
namespace App\Filters\BoardGame;

use App\Filters\Concerns\HasFilters;
use App\Models\BoardGame\PlayerGame;

class BgPlayerGameFilter
{
    use HasFilters;

    private const MODEL = PlayerGame::class;
    private const TABLE_NAME = PlayerGame::TABLE_NAME;
}
