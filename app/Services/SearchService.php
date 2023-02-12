<?php

namespace App\Services;

use App\Http\Resources\SearchArticleResource;
use App\Http\Resources\SearchYouTubeResource;
use App\Models\SearchLog;
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

        $search = DB::table($arParams['tableName']);

        if (!empty($arParams['selectedColumns'])) {
            $search->select($arParams['selectedColumns']);
        }

        if (count($arParams['searchableColumns']) > 1) {
            foreach ($arParams['searchableColumns'] as $key => $searchableColumn) {
                if ($key === 0)
                    $search->where($searchableColumn, 'like', '%' . $query . '%');
                else
                    $search->orWhere($searchableColumn, 'like', '%' . $query . '%');
            }
        } else {
            $search->where($arParams['searchableColumns'][0], 'like', '%' . $query . '%');
        }

        $total = $search->count();

        $search
            ->limit($request->get('limit', 5))
            ->offset($request->get('limit', 5) * ($request->get('page', 1) - 1));

        $searchRes = $search->get();

        if (isset($arParams['resource'])) {
            $returnData['data'] = $arParams['resource']::collection($searchRes);
            $returnData['pagination'] = array('total' => $total);
        } else {
            foreach ($searchRes as $value) {
                $returnData[] = $value;
            }
        }

        return $returnData;
    }
}
