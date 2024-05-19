<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticleListResource;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\VotesCountResource;
use App\Models\Article;
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
        } else {
            $articlesQuery->orderBy('created_at', 'desc');
        }

        $articlesQuery->orderBy('created_at', 'desc');
        $result = $articlesQuery->paginate($request->perPage ?? 4);

        return ArticleListResource::collection($result);
    }

    public function getBySlug(Request $request, $slug) {
        $query = Article::query();

        $query->where('slug', $slug);

        if (isset($request->type)) {
            $query->where('type', $request->type);
        }

        $article = $query->first();

        if ($article) {
            ViewsLogService::set($request, $article->model, $article->id);

            return response()->json(ArticleResource::make($article))->setStatusCode(Response::HTTP_OK);
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
