<?php

namespace App\Http\Controllers\Admin\BoardGame;

use App\Http\Requests\BoardGame\BgPlayerPositionRequest;
use App\Models\BoardGame\BoardGamePlayerPosition;
use App\Http\Controllers\Controller;
use App\Services\Entity\DefaultAdminEntityService;
use App\Traits\HasBaseAdminFunc;

class BgPlayerPositionController extends Controller
{
    use HasBaseAdminFunc;

    private const MODEL = BoardGamePlayerPosition::class;
    private const CACHE_SERVICE = BoardGamePlayerPosition::CACHE_SERVICE;
    private const FILTER = BoardGamePlayerPosition::FILTER;
    private const DETAIL_RESOURCE = BoardGamePlayerPosition::DETAIL_RESOURCE;
    private const LIST_RESOURCE = BoardGamePlayerPosition::LIST_RESOURCE;
    private const SERVICE = BoardGamePlayerPosition::SERVICE;

    protected DefaultAdminEntityService $defaultAdminEntityService;

    public function __construct(DefaultAdminEntityService $defaultAdminEntityService)
    {
        $this->defaultAdminEntityService = $defaultAdminEntityService;
    }

    public function store(BgPlayerPositionRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            self::MODEL
        );
    }

    public function update(BgPlayerPositionRequest $request, BoardGamePlayerPosition $boardGamePlayerPosition)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $boardGamePlayerPosition
        );
    }

    public function destroy(BoardGamePlayerPosition $boardGamePlayerPosition)
    {
        return $this->defaultAdminEntityService->destroy($boardGamePlayerPosition);
    }
}
