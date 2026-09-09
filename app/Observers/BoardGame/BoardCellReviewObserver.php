<?php

namespace App\Observers\BoardGame;

use App\Models\BoardGame\BoardCellReview;
use App\Services\Observer\DefaultObserverService;

class BoardCellReviewObserver
{
    private const CACHE_SERVICE = BoardCellReview::CACHE_SERVICE;
    private const SERVICE = BoardCellReview::SERVICE;
    private const CREATE_VERSION = BoardCellReview::CREATE_VERSION;

    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    public function created(BoardCellReview $BoardCellReview): void
    {
        $this->additionalActions($BoardCellReview);

        $this->defaultObserverService->created(
            $BoardCellReview,
            self::CACHE_SERVICE,
            self::SERVICE,
            self::CREATE_VERSION,
        );
    }

    public function updated(BoardCellReview $BoardCellReview): void
    {
        $this->additionalActions($BoardCellReview);

        $this->defaultObserverService->updated(
            $BoardCellReview,
            self::CACHE_SERVICE,
            self::SERVICE,
            true,
            self::CREATE_VERSION,
        );
    }

    public function deleted(BoardCellReview $BoardCellReview): void
    {
        $this->additionalActions($BoardCellReview);

        $this->defaultObserverService->deleted(
            $BoardCellReview,
            self::CACHE_SERVICE,
            self::SERVICE,
            true,
            self::CREATE_VERSION,
        );
    }

    public function restored(BoardCellReview $BoardCellReview): void
    {
        $this->additionalActions($BoardCellReview);

        $this->defaultObserverService->restored(
            $BoardCellReview,
            self::CACHE_SERVICE,
            self::SERVICE,
            true,
            self::CREATE_VERSION,
        );
    }

    public function forceDeleted(BoardCellReview $BoardCellReview): void
    {
        $this->additionalActions($BoardCellReview);

        $this->defaultObserverService->forceDeleted(
            $BoardCellReview,
            self::CACHE_SERVICE
        );
    }

    private function additionalActions(BoardCellReview $BoardCellReview): void
    {
        $BoardCellReview->load([]);

        $this->clearRelatedCache($BoardCellReview);
    }

    private function clearRelatedCache(BoardCellReview $BoardCellReview): void
    {
        $service = app(self::CACHE_SERVICE);
        $service->clearCellReviewsList($BoardCellReview);
    }
}
