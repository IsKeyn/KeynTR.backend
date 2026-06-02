<?php
namespace App\Http\Controllers\Admin\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Requests\BoardGame\BoardGameRequest;
use App\Models\BoardGame\BoardGame;
use App\Services\Entity\DefaultAdminEntityService;
use App\Traits\HasBaseAdminFunc;
use Illuminate\Http\Request;

class BoardGameController extends Controller
{
    use HasBaseAdminFunc;

    private const MODEL = BoardGame::class;
    private const CACHE_SERVICE = 'App\Services\Cache\BoardGame\BoardGameCacheService';
    private const FILTER = 'App\Filters\BoardGame\BoardGameFilter';
    private const DETAIL_RESOURCE = 'App\Http\Resources\Admin\BoardGame\DetailResource';
    private const LIST_RESOURCE = 'App\Http\Resources\Admin\BoardGame\ListResource';
    private const SERVICE = 'App\Services\BoardGame\BoardGameService';

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

    public function store(BoardGameRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            self::MODEL
        );
    }

    public function update(BoardGameRequest $request, BoardGame $boardGame)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $boardGame
        );
    }

    public function destroy(BoardGame $boardGame)
    {
        return $this->defaultAdminEntityService->destroy($boardGame);
    }
}
