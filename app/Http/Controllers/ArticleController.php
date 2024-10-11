<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticleListResource;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\VotesCountResource;
use App\Models\Article;
use App\Models\Page;
use App\Services\ViewsLogService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ArticleController extends Controller
{
    protected $model = Article::class;

    public function getList(Request $request) {

        $articlesQuery = Article::query();

        if ($filter = $request->filter) {
            $articlesQuery->when(isset($filter['type']), function ($query) use ($filter, $request) {
                $query->where('type', $filter['type']);
            });

            $articlesQuery->when(isset($filter['entity_type']), function ($query) use ($filter, $request) {
                $query->where('entity_type', $filter['entity_type']);
            });

            $articlesQuery->when(isset($filter['entity_id']), function ($query) use ($filter, $request) {
                $query->where('entity_id', $filter['entity_id']);
            });
        }

        $articlesQuery->where('active', true);

        $articlesQuery->orderBy('created_at', 'desc');
        $result = $articlesQuery->paginate($request->perPage ?? 4);

        return ArticleListResource::collection($result);
    }

    public function getById(Request $request, $id) {
        return $this->getArticle($request, $id, 'id');
    }

    public function getBySlug(Request $request, $slug) {
        return $this->getArticle($request, $slug, 'slug');
    }

    public function getArticle($request, $param, $type) {
        $validated = $request->validate([
            'type' => 'nullable|int',
            'entity_type' => 'nullable|string',
            'entity_id' => 'nullable|int',
            'full_path' => 'nullable|string',
        ]);

        if ($param) {
            $query = Article::query();

            if (isset($request->type)) {
                $query->where('type', $request->type);
            }

            if (isset($validated['full_path'])) {
                $delimitedString = explode('/', trim($validated['full_path'], '/'));

                if (isset($delimitedString[0]) && $delimitedString[1] && isset(Page::PAGE_TO_ENTITY[$delimitedString[0]])) {
                    $entity = Page::PAGE_TO_ENTITY[$delimitedString[0]];
                    $entityField = $entity::where('slug', $delimitedString[1])->select('id')->first();

                    if ($entityField->id) {
                        $query->where('entity_type', $entity)->where('entity_id', $entityField->id);
                    } else {
                        return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);
                    }
                } else {
                    return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);
                }
            } elseif (isset($validated['entity_type']) && isset($validated['entity_id'])) {
                $query->where('entity_type', $validated['entity_type'])->where('entity_id',
                    $validated['entity_id']);
            }

            $query->where($type, $param);

            $article = $query->first();

            if ($article) {
                ViewsLogService::set($request, $article->model, $article->id);

                return response()->json(ArticleResource::make($article))->setStatusCode(Response::HTTP_OK);
            } else {
                return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);
            }
        } else {
            return response()->json()->setStatusCode(Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request) {
        echo '<pre>';
        print_r($request);
        echo '</pre>';
    }
}
