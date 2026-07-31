<?php

namespace App\Http\Controllers\Admin\BoardGame;

use App\Http\Requests\BoardGame\BgPlayerStatusEffectRequest;
use App\Models\BoardGame\PlayerStatusEffect;
use App\Http\Controllers\Controller;
use App\Services\Entity\DefaultAdminEntityService;
use App\Traits\HasBaseAdminFunc;
use Illuminate\Http\Request;

class BgPlayerStatusEffectController extends Controller
{
    use HasBaseAdminFunc;

    private const MODEL = PlayerStatusEffect::class;
    private const CACHE_SERVICE = PlayerStatusEffect::CACHE_SERVICE;
    private const FILTER = PlayerStatusEffect::FILTER;
    private const DETAIL_RESOURCE = PlayerStatusEffect::DETAIL_RESOURCE;
    private const LIST_RESOURCE = PlayerStatusEffect::LIST_RESOURCE;
    private const SERVICE = PlayerStatusEffect::SERVICE;

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

    public function store(BgPlayerStatusEffectRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            self::MODEL
        );
    }

    public function update(BgPlayerStatusEffectRequest $request, PlayerStatusEffect $playerStatusEffect)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $playerStatusEffect
        );
    }

    public function destroy(PlayerStatusEffect $playerStatusEffect)
    {
        return $this->defaultAdminEntityService->destroy($playerStatusEffect);
    }
}
