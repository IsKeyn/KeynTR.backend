<?php

namespace App\Filters;

use App\Filters\Concerns\HasFilters;
use App\Models\Person\Person;
use App\Models\ViewsCount;
use App\Models\VotesCount;

class PersonFilter
{
    use HasFilters;

    private const MODEL = 'App\Models\Person';
    private const TABLE_NAME = 'people';

    protected function sort($value): void
    {
        if ($value) {
            switch ($value->field) {
                case 'likes':
                    $this->query
                        ->with('likes')
                        ->orderBy(
                            VotesCount::select('value')
                                ->whereColumn('entity_id', 'people.id')
                                ->where('entity_type', Person::class)
                                ->limit(1),
                            $value->sort
                        );
                    break;
                case 'views':
                    $this->query
                        ->with('views')
                        ->orderBy(
                            ViewsCount::select('value')
                                ->whereColumn('entity_id', 'people.id')
                                ->where('entity_type', Person::class)
                                ->limit(1),
                            $value->sort
                        );
                    break;
                case 'date':
                    $this->query
                        ->with('dates')
                        ->withAggregate('dates', 'date')
                        ->orderBy('dates_date', $value->sort);
                    break;

                case 'sort':
                    $this->query->orderByRaw('sort IS NULL, sort ' . $value->sort);
                    break;

                default:
                    $this->query->orderBy($value->field, $value->sort);
                    break;
            }

            $field = strtolower($value->field);
            if ($field !== 'id') {
                $this->query->orderBy('id', 'desc');
            }
        }
    }
}
