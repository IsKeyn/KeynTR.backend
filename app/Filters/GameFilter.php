<?php
namespace App\Filters;

use App\Models\Game;
use App\Models\ViewsCount;
use App\Models\VotesCount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class GameFilter
{
    protected $request;
    protected $query;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply(Builder $query)
    {
        $this->query = $query;

        foreach ($this->filters() as $filter => $value) {
            if (method_exists($this, $filter)) {
                $this->$filter($value);
            }
        }

        return $this->query;
    }

    public function filters()
    {
        return (array) json_decode($this->request->filters);
    }

    protected function category($value)
    {
        $this->query->where('category_id', $value);
    }

    protected function price_min($value)
    {
        $this->query->where('price', '>=', $value);
    }

    protected function price_max($value)
    {
        $this->query->where('price', '<=', $value);
    }

    protected function tags($value)
    {
        if ($value && $tags = $value) {
            $this->query
                ->with('tags')
                ->whereHas('tags', function($query) use ($tags) {
                    $query->whereIn('tags.name', $tags);
                });
        }
    }

    protected function gamePlatforms($value)
    {
        if ($value && $gamePlatformsIds = $value) {
            $this->query
                ->with('gamePlatform')
                ->whereHas('gamePlatform', function($query) use ($gamePlatformsIds) {
                    $query->whereIn('gaming_platforms.id', $gamePlatformsIds);
                });
        }
    }

    protected function genres($value)
    {
        if ($value && $genresIds = $value) {
            $this->query
                ->with('genres')
                ->whereHas('genres', function($query) use ($genresIds) {
                    $query->whereIn('genres.id', $genresIds);
                });
        }
    }

    protected function companies($value)
    {
        if ($value && $companiesIds = $value) {
            $this->query
                ->with('company')
                ->whereHas('company', function($query) use ($companiesIds) {
                    $query->whereIn('companies.id', $companiesIds);
                });
        }
    }

    protected function date_min($value)
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

    protected function date_max($value)
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

    protected function search($value)
    {
        if ($value) {
            $this->query->where('name', 'like', '%' . $value . '%');
        }
    }

    protected function sort($value)
    {
        if ($value) {
            switch ($value->field) {
                case 'likes':
                    $this->query
                        ->with('likes')
                        ->orderBy(
                            VotesCount::select('value')
                                ->whereColumn('entity_id', 'games.id')
                                ->where('entity_type', Game::class)
                                ->limit(1),
                            $value->sort
                        );
                    break;
                case 'views':
                    $this->query
                        ->with('views')
                        ->orderBy(
                            ViewsCount::select('value')
                                ->whereColumn('entity_id', 'games.id')
                                ->where('entity_type', Game::class)
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
