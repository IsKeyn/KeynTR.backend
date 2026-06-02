<?php

namespace App\Http\Controllers\Admin\BoardGame;

use App\Http\Requests\BoardGame\BoardPositionEffectBindRequest;
use App\Models\BoardGame\BoardPositionEffectsBind;
use App\Http\Controllers\Controller;
use App\Services\Entity\DefaultAdminEntityService;
use App\Traits\HasBaseAdminFunc;
use Illuminate\Http\Request;

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

    public function index(Request $request)
    {
        return $this->defaultAdminEntityService->index(
            $request,
            self::MODEL,
            self::CACHE_SERVICE,
            self::FILTER,
            self::LIST_RESOURCE,
            false
        );
    }

    public function store(BoardPositionEffectBindRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            self::MODEL
        );
    }

    public function update(BoardPositionEffectBindRequest $request, BoardPositionEffectsBind $boardPositionEffectsBind)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $boardPositionEffectsBind
        );
    }

    public function destroy(BoardPositionEffectsBind $boardPositionEffectsBind)
    {
        return $this->defaultAdminEntityService->destroy($boardPositionEffectsBind);
    }
}
