<?php
namespace App\Filters;

use App\Models\Game;
use App\Models\ViewsCount;
use App\Models\VotesCount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class VersionFilter
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

    protected function sort($value)
    {
        if ($value) {
            switch ($value->field) {
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
