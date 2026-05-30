<?php

namespace App\Http\Controllers\Admin\BoardGame;

use App\Http\Requests\BoardGame\InventoryRequest;
use App\Models\BoardGame\BoardGameInventory;
use App\Http\Controllers\Controller;
use App\Services\Entity\DefaultAdminEntityService;
use App\Traits\HasBaseAdminFunc;

class InventoryController extends Controller
{
    use HasBaseAdminFunc;

    private const MODEL = BoardGameInventory::class;
    private const CACHE_SERVICE = BoardGameInventory::CACHE_SERVICE;
    private const FILTER = BoardGameInventory::FILTER;
    private const DETAIL_RESOURCE = BoardGameInventory::DETAIL_RESOURCE;
    private const LIST_RESOURCE = BoardGameInventory::LIST_RESOURCE;
    private const SERVICE = BoardGameInventory::SERVICE;

    protected DefaultAdminEntityService $defaultAdminEntityService;

    public function __construct(DefaultAdminEntityService $defaultAdminEntityService)
    {
        $this->defaultAdminEntityService = $defaultAdminEntityService;
    }

    public function store(InventoryRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            self::MODEL
        );
    }

    public function update(InventoryRequest $request, BoardGameInventory $boardGameInventory)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $boardGameInventory
        );
    }

    public function destroy(BoardGameInventory $boardGameInventory)
    {
        return $this->defaultAdminEntityService->destroy($boardGameInventory);
    }
}
