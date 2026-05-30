<?php
namespace App\Http\Controllers\Admin\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Requests\BoardGame\BgGameListRequest;
use App\Models\BoardGame\BoardGameGameList;
use App\Services\Entity\DefaultAdminEntityService;
use App\Traits\HasBaseAdminFunc;

class BgGameListController extends Controller
{
    use HasBaseAdminFunc;

    private const MODEL = BoardGameGameList::class;
    private const CACHE_SERVICE = BoardGameGameList::CACHE_SERVICE;
    private const FILTER = BoardGameGameList::FILTER;
    private const DETAIL_RESOURCE = BoardGameGameList::DETAIL_RESOURCE;
    private const LIST_RESOURCE = BoardGameGameList::LIST_RESOURCE;
    private const SERVICE = BoardGameGameList::SERVICE;

    protected DefaultAdminEntityService $defaultAdminEntityService;

    public function __construct(DefaultAdminEntityService $defaultAdminEntityService)
    {
        $this->defaultAdminEntityService = $defaultAdminEntityService;
    }

    public function store(BgGameListRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            self::MODEL
        );
    }

    public function update(BgGameListRequest $request, BoardGameGameList $gameList)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $gameList
        );
    }

    public function destroy(BoardGameGameList $gameList)
    {
        return $this->defaultAdminEntityService->destroy($gameList);
    }
}
