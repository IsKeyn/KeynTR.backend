<?php

namespace App\Http\Controllers\Admin\BoardGame;

use App\Http\Requests\BoardGame\BoardPositionEffectRequest;
use App\Models\BoardGame\BoardPositionEffectsBind;
use App\Http\Controllers\Controller;
use App\Services\Entity\DefaultAdminEntityService;
use App\Traits\HasBaseAdminFunc;
use Illuminate\Http\JsonResponse;

class BoardPositionEffectBindController extends Controller
{
    use HasBaseAdminFunc;

    private const MODEL = BoardPositionEffectsBind::class;
    private const CACHE_SERVICE = BoardPositionEffectsBind::CACHE_SERVICE;
    private const FILTER = BoardPositionEffectsBind::FILTER;
    private const DETAIL_RESOURCE = BoardPositionEffectsBind::DETAIL_RESOURCE;
    private const LIST_RESOURCE = BoardPositionEffectsBind::LIST_RESOURCE;
    private const SERVICE = BoardPositionEffectsBind::SERVICE;

    protected DefaultAdminEntityService $defaultAdminEntityService;

    public function __construct(DefaultAdminEntityService $defaultAdminEntityService)
    {
        $this->defaultAdminEntityService = $defaultAdminEntityService;
    }

    public function store(BoardPositionEffectRequest $request): JsonResponse
    {
        return $this->defaultAdminEntityService->store(
            $request,
            self::MODEL
        );
    }

    public function update(BoardPositionEffectRequest $request, BoardPositionEffectsBind $boardPositionEffectBind)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $boardPositionEffectBind
        );
    }

    public function destroy(BoardPositionEffectsBind $boardPositionEffectBind)
    {
        return $this->defaultAdminEntityService->destroy($boardPositionEffectBind);
    }
}
