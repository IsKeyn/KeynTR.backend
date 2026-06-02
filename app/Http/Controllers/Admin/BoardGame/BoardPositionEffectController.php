<?php

namespace App\Http\Controllers\Admin\BoardGame;

use App\Http\Requests\BoardGame\BoardPositionEffectRequest;
use App\Models\BoardGame\BoardPositionEffect;
use App\Http\Controllers\Controller;
use App\Services\Entity\DefaultAdminEntityService;
use App\Traits\HasBaseAdminFunc;
use Illuminate\Http\Request;

class BoardPositionEffectController extends Controller
{
    use HasBaseAdminFunc;

    private const MODEL = BoardPositionEffect::class;
    private const CACHE_SERVICE = BoardPositionEffect::CACHE_SERVICE;
    private const FILTER = BoardPositionEffect::FILTER;
    private const DETAIL_RESOURCE = BoardPositionEffect::DETAIL_RESOURCE;
    private const LIST_RESOURCE = BoardPositionEffect::LIST_RESOURCE;
    private const SERVICE = BoardPositionEffect::SERVICE;

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
            true,
            ['media']
        );
    }

    public function store(BoardPositionEffectRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            self::MODEL
        );
    }

    public function update(BoardPositionEffectRequest $request, BoardPositionEffect $boardPositionEffect)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $boardPositionEffect
        );
    }

    public function destroy(BoardPositionEffect $boardPositionEffect)
    {
        return $this->defaultAdminEntityService->destroy($boardPositionEffect);
    }
}
