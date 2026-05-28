<?php
namespace App\Http\Controllers\Admin\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Requests\BoardGame\BgPlayerGameRequest;
use App\Models\BoardGame\PlayerGame;
use App\Services\Entity\DefaultAdminEntityService;
use App\Traits\HasBaseAdminFunc;
use Illuminate\Http\JsonResponse;

class BgPlayerGameController extends Controller
{
    use HasBaseAdminFunc;

    private const MODEL = PlayerGame::class;
    private const CACHE_SERVICE = PlayerGame::CACHE_SERVICE;
    private const FILTER = PlayerGame::FILTER;
    private const DETAIL_RESOURCE = PlayerGame::DETAIL_RESOURCE;
    private const LIST_RESOURCE = PlayerGame::LIST_RESOURCE;
    private const SERVICE = PlayerGame::SERVICE;

    protected DefaultAdminEntityService $defaultAdminEntityService;

    public function __construct(DefaultAdminEntityService $defaultAdminEntityService)
    {
        $this->defaultAdminEntityService = $defaultAdminEntityService;
    }

    public function store(BgPlayerGameRequest $request): JsonResponse
    {
        return $this->defaultAdminEntityService->store(
            $request,
            self::MODEL
        );
    }

    public function update(BgPlayerGameRequest $request, PlayerGame $playerGame)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $playerGame
        );
    }

    public function destroy(PlayerGame $playerGame)
    {
        return $this->defaultAdminEntityService->destroy($playerGame);
    }
}
