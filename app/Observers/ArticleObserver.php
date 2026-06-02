<?php

namespace App\Observers;

use App\Models\Article;
use App\Services\Observer\DefaultObserverService;

class ArticleObserver
{
    private const CACHE_SERVICE = 'App\Services\Cache\ArticleCacheService';
    private const SERVICE = 'App\Services\ArticleService';

    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    public function created(Article $article)
    {
        $this->defaultObserverService->created(
            $article,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function updated(Article $article)
    {
        $this->defaultObserverService->updated(
            $article,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function deleted(Article $article)
    {
        $this->defaultObserverService->deleted(
            $article,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function restored(Article $article)
    {
        $this->defaultObserverService->restored(
            $article,
            self::CACHE_SERVICE,
            self::SERVICE
        );
    }

    public function forceDeleted(Article $article)
    {
        $this->defaultObserverService->forceDeleted(
            $article,
            self::CACHE_SERVICE
        );
    }
}
