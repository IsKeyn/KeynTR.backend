<?php

namespace App\Http\Controllers\Admin\BoardGame;

use App\Http\Requests\BoardGame\BgStatusEffectBindRequest;
use App\Models\BoardGame\StatusEffectBind;
use App\Http\Controllers\Controller;
use App\Services\Entity\DefaultAdminEntityService;
use App\Traits\HasBaseAdminFunc;
use Illuminate\Http\Request;

class StatusEffectBindController extends Controller
{
    use HasBaseAdminFunc;

    private const MODEL = StatusEffectBind::class;
    private const CACHE_SERVICE = StatusEffectBind::CACHE_SERVICE;
    private const FILTER = StatusEffectBind::FILTER;
    private const DETAIL_RESOURCE = StatusEffectBind::DETAIL_RESOURCE;
    private const LIST_RESOURCE = StatusEffectBind::LIST_RESOURCE;
    private const SERVICE = StatusEffectBind::SERVICE;

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

    public function store(BgStatusEffectBindRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            self::MODEL
        );
    }

    public function update(BgStatusEffectBindRequest $request, StatusEffectBind $statusEffectBind)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $statusEffectBind
        );
    }

    public function destroy(StatusEffectBind $statusEffectBind)
    {
        return $this->defaultAdminEntityService->destroy($statusEffectBind);
    }
}
