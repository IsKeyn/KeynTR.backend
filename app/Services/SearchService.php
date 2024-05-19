<?php

namespace App\Services;

use App\Http\Resources\ArticleListResource;
use App\Http\Resources\SearchYouTubeResource;
use App\Models\Article;
use App\Models\SearchLog;
use App\Models\YoutubeVideo;
use Illuminate\Http\Request;

class SearchService
{
    public static $arSearchTables = array(
        [
            'entity' => YoutubeVideo::class,
            'searchableColumns' => ['title'],
            'resource' => SearchYouTubeResource::class,
        ],
        [
            'entity' => Article::class,
            'searchableColumns' => ['name', 'text_full'],
            'resource' => ArticleListResource::class,
        ]
    );

    public static function search($query, Request $request) {
        $searchReturnData = array();

        if ($query) {
            // Запись логов поиска
            $searchLog = ['query' => $query];

            if ($user = $request->user()) {
                $searchLog['created_by'] = $user->id;
            }

            SearchLog::create($searchLog);

            // Формирование результатов пиоска
            $searchReturnData = array();

            if ($searchAbleEntity = $request->get('entity')) {
                $entityKey = null;

                foreach (self::$arSearchTables as $key => $table) {
                    if ($table['entity'] === $searchAbleEntity) {
                        $entityKey = $key;
                        break;
                    }
                }

                if ($entityKey !== null) {
                    $searchReturnData = self::searchInTable($query, $entityKey, $request);
                }
            } else {
                foreach (self::$arSearchTables as $key => $table) {
                    $searchReturnData[$table['entity']] = self::searchInTable($query, $key, $request);
                }
            }
        } else {
            $query['errors'][] = 'Поисковая строка пуста';
        }

        return $searchReturnData;
    }

    public static function searchInTable($query, $entityKey) {
        $arParams = self::$arSearchTables[$entityKey];

        $params = array(
            'arParams' => $arParams,
            'query' => $query,
        );

        $search = $arParams['entity']::query()
            ->when($params, function ($query) use ($params) {
                if (count($params['arParams']['searchableColumns']) > 1) {
                    foreach ($params['arParams']['searchableColumns'] as $key => $searchableColumn) {
                        if ($key === 0)
                            $query->where($searchableColumn, 'like', '%' . $params['query'] . '%');
                        else
                            $query->orWhere($searchableColumn, 'like', '%' . $params['query'] . '%');
                    }
                } else {
                    $query->where($params['arParams']['searchableColumns'][0], 'like', '%' . $params['query'] . '%');
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
