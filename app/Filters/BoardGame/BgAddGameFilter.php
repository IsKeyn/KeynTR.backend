<?php
namespace App\Filters\BoardGame;

use App\Filters\Concerns\HasFilters;
use App\Models\BoardGame\AddGame;

class BgAddGameFilter
{
    use HasFilters;

    private const MODEL = AddGame::class;
    private const TABLE_NAME = AddGame::TABLE_NAME;
}
