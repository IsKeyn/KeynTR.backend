<?php

namespace App\Http\Controllers\Admin\BoardGame;

use App\Http\Requests\BoardGame\BgPlayerInteractionRequest;
use App\Models\BoardGame\PlayerInteractions;
use App\Http\Controllers\Controller;
use App\Services\Entity\DefaultAdminEntityService;
use App\Traits\HasBaseAdminFunc;
use Illuminate\Http\Request;


class BgPlayerInteractionController extends Controller
{
    use HasBaseAdminFunc;

    private const MODEL = PlayerInteractions::class;
    private const CACHE_SERVICE = PlayerInteractions::CACHE_SERVICE;
    private const FILTER = PlayerInteractions::FILTER;
    private const DETAIL_RESOURCE = PlayerInteractions::DETAIL_RESOURCE;
    private const LIST_RESOURCE = PlayerInteractions::LIST_RESOURCE;
    private const SERVICE = PlayerInteractions::SERVICE;

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
            false,
        );
    }

    public function store(BgPlayerInteractionRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            self::MODEL
        );
    }

    public function update(BgPlayerInteractionRequest $request, PlayerInteractions $playerInteraction)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $playerInteraction
        );
    }

    public function destroy(PlayerInteractions $playerInteraction)
    {
        return $this->defaultAdminEntityService->destroy($playerInteraction);
    }
}
