<?php
namespace App\Filters\BoardGame;

use App\Filters\Concerns\HasFilters;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\BoardGamePlayerPosition;
use App\Models\User;
use App\Models\ViewsCount;
use App\Models\VotesCount;
use App\Services\TwitchService;
use Illuminate\Database\Eloquent\Builder;

class BgPlayerFilter
{
    use HasFilters {
        apply as traitApply;
    }

    private const MODEL = BoardGamePlayer::class;
    private const TABLE_NAME = BoardGamePlayer::TABLE_NAME;

    public function apply(Builder $query): Builder
    {
        $filters = $this->filters();
        $onlyInactive = isset($filters['only_inactive'])
            && filter_var($filters['only_inactive'], FILTER_VALIDATE_BOOLEAN);

        if ($onlyInactive) {
            $query->where('active', false);
        } else {
            $query->where('active', true);
        }

        return $this->traitApply($query);
    }

    protected function search($value): void
    {
        if ($value) {
            $this->query->whereHas('user', function (Builder $query) use ($value) {
                $query->where('public_name', 'like', '%' . $value . '%');
            });
        }
    }

    protected function twitchStreamOnline($value): void
    {
        if ($value) {
            $twitchService = app(TwitchService::class);
            $result = $twitchService->streamersLive();
            $this->query->whereIn('user_id', array_column($result, 'site_user_id'));
        }
    }

    protected function notActive($value): void
    {
        if ($value) {
            $twitchService = app(TwitchService::class);
            $result = $twitchService->streamersLive();
            $this->query->whereIn('user_id', array_column($result, 'site_user_id'));
        }
    }

    protected function sort($value): void
    {
       if ($value) {
           switch ($value->field) {
               case 'name':
                   $this->query->orderBy(
                       User::select('public_name')
                           ->whereColumn('users.id', self::TABLE_NAME . '.user_id')
                           ->limit(1),
                       $value->sort ?? 'asc'
                   );
                   break;
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

               case 'full_points':
                   // Создаем подзапрос, имитирующий ->first()
                   $table = self::TABLE_NAME;

                   $positionSubquery = BoardGamePlayerPosition::select('position')
                       ->whereColumn('board_game_id', $table . '.id')
                       ->limit(1);

                   $subquerySql = $positionSubquery->toSql();

                   // Формируем выражение: points + COALESCE(подзапрос, 0)
                   $orderByExpression = "({$table}.points + COALESCE(({$subquerySql}), 0))";

                   $direction = (isset($value->sort) && strtolower($value->sort) === 'asc') ? 'ASC' : 'DESC';

                    $this->query->orderByRaw(
                        "{$orderByExpression} {$direction}",
                        $positionSubquery->getBindings()
                    );
                    break;

               default:
                   $this->query->orderBy($value->field, $value->sort);
                   break;
           }
       }
    }
}
