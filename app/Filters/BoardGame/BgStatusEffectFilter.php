<?php
namespace App\Filters\BoardGame;

use App\Filters\Concerns\HasFilters;
use App\Models\BoardGame\StatusEffect;

class BgStatusEffectFilter
{
    use HasFilters;

    private const MODEL = StatusEffect::class;
    private const TABLE_NAME = StatusEffect::TABLE_NAME;
}
