<?php

namespace App\Filters;

use App\Filters\Concerns\HasFilters;
use App\Models\Game;
use App\Models\ViewsCount;
use App\Models\VotesCount;

class GameFilter
{
    use HasFilters;

    protected function gamePlatforms($value): void
    {
        if ($value && $gamePlatformsIds = $value) {
            $this->query
                ->with('gamePlatform')
                ->whereHas('gamePlatform', function($query) use ($gamePlatformsIds) {
                    $query->whereIn('gaming_platforms.id', $gamePlatformsIds);
                });
        }
    }

    protected function genres($value): void
    {
        if ($value && $genresIds = $value) {
            $this->query
                ->with('genres')
                ->whereHas('genres', function($query) use ($genresIds) {
                    $query->whereIn('genres.id', $genresIds);
                });
        }
    }

    protected function companies($value): void
    {
        if ($value && $companiesIds = $value) {
            $this->query
                ->with('company')
                ->whereHas('company', function($query) use ($companiesIds) {
                    $query->whereIn('companies.id', $companiesIds);
                });
        }
    }

    protected function series($value): void
    {
        if ($value) {
            $this->query
                ->with('series')
                ->whereHas('series', function($query) use ($value) {
                    $query->whereIn('series.id', $value);
                });
        }
    }

    protected function events($value): void
    {
        if ($value) {
            $this->query
                ->with('bgGamesList', 'bgGamesList.boardGame')
                ->whereHas('bgGamesList', function($query_0) use ($value) {
                    $query_0->whereHas('boardGame', function($query_1) use ($value) {
                        $query_1->whereIn('board_games.id', $value);
                    });
                });
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
