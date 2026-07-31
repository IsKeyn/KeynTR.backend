<?php

namespace App\Filters\Concerns;

use App\Models\ViewsCount;
use App\Models\VotesCount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

trait HasFilters
{
    protected Request $request;
    protected Builder $query;

    protected string $searchColumn = 'name';
    protected string $filterKey = 'filters';

    public function __construct(Request $request, string $filterKey = null)
    {
        $this->request = $request;

        if ($filterKey !== null) {
            $this->filterKey = $filterKey;
        }
    }

    public function apply(Builder $query): Builder
    {
        $this->query = $query;

        foreach ($this->filters() as $filter => $value) {
            if (method_exists($this, $filter)) {
                $this->$filter($value);
            }
        }

        return $this->query;
    }

    public function filters(): array
    {
        return (array) json_decode($this->request->input($this->filterKey));
    }

    protected function category($value): void
    {
        $this->query->where('category_id', $value);
    }

    protected function price_min($value): void
    {
        $this->query->where('price', '>=', $value);
    }

    protected function price_max($value): void
    {
        $this->query->where('price', '<=', $value);
    }

    protected function tags($value): void
    {
        if ($value && $tags = $value) {
            $this->query
                ->with('tags')
                ->whereHas('tags', function($query) use ($tags) {
                    $query->whereIn('tags.name', $tags);
                });
        }
    }

    protected function withTrashed($value): void
    {
        if ($value) {
            $this->query->withTrashed();
        }
    }

    protected function onlyTrashed($value): void
    {
        if ($value) {
            $this->query->onlyTrashed();
        }
    }

    protected function search($value): void
    {
        if ($value) {
            $this->query->where($this->searchColumn, 'like', '%' . $value . '%');
        }
    }

    protected function date_min($value): void
    {
        if ($value) {
            if (isset($this->filters()['by_first_date']) && filter_var($this->filters()['by_first_date'], FILTER_VALIDATE_BOOLEAN)) {
                $this->query
                    ->with('dates')
                    ->withAggregate('dates', 'date', 'min')
                    ->having('dates_min_date', '>=', Carbon::createFromDate($value)->startOfYear());
            } else {
                $this->query
                    ->with('dates')
                    ->whereHas('dates', function($query) use ($value) {
                        $query->where('date', '>=', Carbon::createFromDate($value)->startOfYear());
                    });
            }
        }
    }

    protected function date_max($value): void
    {
        if ($value) {
            if (!(isset($this->filters()['date_min']) && $this->filters()['date_min'])) {
                $this->query->with('dates');
            }

            if (isset($this->filters()['by_first_date']) && filter_var($this->filters()['by_first_date'], FILTER_VALIDATE_BOOLEAN)) {
                if (!(isset($this->filters()['date_min']) && $this->filters()['date_min'])) {
                    $this->query->withAggregate('dates', 'date', 'min');
                }
                $this->query->having('dates_min_date', '<=', Carbon::createFromDate($value)->endOfYear());
            } else {
                $this->query->whereHas('dates', function($query) use ($value) {
                    $query->where('date', '<=', Carbon::createFromDate($value)->endOfYear());
                });
            }
        }
    }

    protected function sort($value): void
    {
        if ($value) {
            switch ($value->field) {
                case 'likes':
                    $this->query
                        ->with('likes')
                        ->orderBy(
                            VotesCount::select('value')
                                ->whereColumn('entity_id', self::TABLE_NAME . '.id')
                                ->where('entity_type', self::MODEL)
                                ->limit(1),
                            $value->sort
                        );
                    break;
                case 'views':
                    $this->query
                        ->with('views')
                        ->orderBy(
                            ViewsCount::select('value')
                                ->whereColumn('entity_id', self::TABLE_NAME . '.id')
                                ->where('entity_type', self::MODEL)
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
        }
    }
}
