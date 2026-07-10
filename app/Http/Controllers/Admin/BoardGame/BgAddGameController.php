<?php
namespace App\Http\Controllers\Admin\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Requests\BoardGame\BgAddGameRequest;
use App\Models\BoardGame\AddGame;
use App\Services\Entity\DefaultAdminEntityService;
use App\Traits\HasBaseAdminFunc;

class BgAddGameController extends Controller
{
    use HasBaseAdminFunc;

    private const MODEL = AddGame::class;
    private const CACHE_SERVICE = AddGame::CACHE_SERVICE;
    private const FILTER = AddGame::FILTER;
    private const DETAIL_RESOURCE = AddGame::DETAIL_RESOURCE;
    private const LIST_RESOURCE = AddGame::LIST_RESOURCE;
    private const SERVICE = AddGame::SERVICE;

    protected DefaultAdminEntityService $defaultAdminEntityService;

    public function __construct(DefaultAdminEntityService $defaultAdminEntityService)
    {
        $this->defaultAdminEntityService = $defaultAdminEntityService;
    }

    public function store(BgAddGameRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            self::MODEL
        );
    }

    public function update(BgAddGameRequest $request, AddGame $addGame)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $addGame
        );
    }

    public function destroy(AddGame $addGame)
    {
        return $this->defaultAdminEntityService->destroy($addGame);
    }
}
