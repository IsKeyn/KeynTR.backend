<?php

namespace App\Http\Controllers\Admin\BoardGame;

use App\Http\Requests\BoardGame\BgStatusEffectRequest;
use App\Models\BoardGame\Item;
use App\Models\BoardGame\StatusEffect;
use App\Http\Controllers\Controller;
use App\Services\Entity\DefaultAdminEntityService;
use App\Traits\HasBaseAdminFunc;
use App\Http\Resources\Admin\BoardGame\ItemResource;
use Illuminate\Http\Request;

class StatusEffectController extends Controller
{
    use HasBaseAdminFunc;

    private const MODEL = StatusEffect::class;
    private const CACHE_SERVICE = StatusEffect::CACHE_SERVICE;
    private const FILTER = StatusEffect::FILTER;
    private const DETAIL_RESOURCE = StatusEffect::DETAIL_RESOURCE;
    private const LIST_RESOURCE = StatusEffect::LIST_RESOURCE;
    private const SERVICE = StatusEffect::SERVICE;

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
            ['titleImage']
        );
    }

    public function store(BgStatusEffectRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            self::MODEL
        );
    }

    public function update(BgStatusEffectRequest $request, StatusEffect $statusEffect)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $statusEffect
        );
    }

    public function destroy(StatusEffect $statusEffect)
    {
        return $this->defaultAdminEntityService->destroy($statusEffect);
    }

    public function list(Item $Item)
    {
        return ItemResource::collection($Item::all());
    }
}
