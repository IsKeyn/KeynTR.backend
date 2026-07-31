<?php
namespace App\Filters;

use App\Filters\Concerns\HasFilters;
use App\Models\Character;

class CharacterFilter
{
    use HasFilters;

    private const MODEL = Character::class;
    private const TABLE_NAME = Character::TABLE_NAME;
}
