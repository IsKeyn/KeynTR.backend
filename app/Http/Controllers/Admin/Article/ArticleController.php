<?php

namespace App\Http\Controllers\Admin\Article;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleRequest;
use App\Models\Article;
use App\Services\Entity\DefaultAdminEntityService;
use App\Traits\HasBaseAdminFunc;

class ArticleController extends Controller
{
    use HasBaseAdminFunc;

    private const MODEL = Article::class;
    private const CACHE_SERVICE = 'App\Services\Cache\ArticleCacheService';
    private const FILTER = 'App\Filters\ArticleFilter';
    private const DETAIL_RESOURCE = 'App\Http\Resources\Admin\Article\DetailResource';
    private const LIST_RESOURCE = 'App\Http\Resources\Admin\Article\ListResource';
    private const SERVICE = 'App\Services\ArticleService';

    protected DefaultAdminEntityService $defaultAdminEntityService;

    public function __construct(DefaultAdminEntityService $defaultAdminEntityService)
    {
        $this->defaultAdminEntityService = $defaultAdminEntityService;
    }

    public function store(ArticleRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            self::MODEL
        );
    }

    public function update(ArticleRequest $request, Article $article)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $article
        );
    }

    public function destroy(Article $article)
    {
        return $this->defaultAdminEntityService->destroy($article);
    }
}
