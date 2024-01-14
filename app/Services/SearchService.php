<?php

namespace App\Services;

use App\Http\Resources\SearchArticleResource;
use App\Http\Resources\SearchYouTubeResource;
use App\Models\SearchLog;
use Google\Service\Blogger\Page;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchService
{
    public static $arSearchTables = array(
        [
            'tableName' => 'youtube_videos',
            'selectedColumns' => [],
            'searchableColumns' => ['title'],
            'resource' => SearchYouTubeResource::class,
        ],
        [
            'tableName' => 'articles',
            'selectedColumns' => [],
            'searchableColumns' => ['title', 'text_full'],
            'resource' => SearchArticleResource::class,
        ]
    );

    public static function search($query, Request $request) {
        $searchReturnData = array();

        if ($query) {
            SearchLog::create(['query' => $query]);

            $searchReturnData = array();

            if ($searchAbleTable = $request->get('table')) {
                $tableKey = null;

                foreach (self::$arSearchTables as $key => $table) {
                    if ($table['tableName'] === $searchAbleTable) {
                        $tableKey = $key;
                        break;
                    }
                }

                if ($tableKey !== null) {
                    $searchReturnData = self::searchInTable($query, $tableKey, $request);
                }
            } else {
                foreach (self::$arSearchTables as $key => $table) {
                    $searchReturnData[$table['tableName']] = self::searchInTable($query, $key, $request);
                }
            }
        } else {
            $query['errors'][] = 'Поисковая строка пуста';
        }

        return $searchReturnData;
    }

    public static function searchInTable($query, $tableKey, $request) {
        $arParams = self::$arSearchTables[$tableKey];

        $params = array(
            'arParams' => $arParams,
            'query' => $query,
        );

        $search = DB::table($arParams['tableName'])
            ->when($params, function (Builder $builder, $params) {
                if (!empty($params['arParams']['selectedColumns'])) {
                    $builder->select($params['arParams']['selectedColumns']);
                }

                if (count($params['arParams']['searchableColumns']) > 1) {
                    foreach ($params['arParams']['searchableColumns'] as $key => $searchableColumn) {
                        if ($key === 0)
                            $builder->where($searchableColumn, 'like', '%' . $params['query'] . '%');
                        else
                            $builder->orWhere($searchableColumn, 'like', '%' . $params['query'] . '%');
                    }
                } else {
                    $builder->where($params['arParams']['searchableColumns'][0], 'like', '%' . $params['query'] . '%');
                }
            })->paginate(6);


        if (isset($arParams['resource'])) {
            $returnData['data'] = $arParams['resource']::collection($search);
            $returnData['pagination'] = array(
                'total' => $search->total(),
                'count' => $search->count(),
                'per_page' => $search->perPage(),
                'current_page' => $search->currentPage(),
                'last_page' => $search->lastPage(),
            );
        } else {
            foreach ($search as $value) {
                $returnData[] = $value;
            }
        }

        return $returnData;
    }
}
