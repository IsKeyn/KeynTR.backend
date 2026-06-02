<?php
namespace App\Http\Controllers\Admin\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Requests\BoardGame\BgPlayerRequest;
use App\Models\BoardGame\BoardGamePlayer;
use App\Services\Entity\DefaultAdminEntityService;
use App\Traits\HasBaseAdminFunc;
use Illuminate\Http\JsonResponse;

class BgPlayerController extends Controller
{
    use HasBaseAdminFunc;

    private const MODEL = BoardGamePlayer::class;
    private const CACHE_SERVICE = BoardGamePlayer::CACHE_SERVICE;
    private const FILTER = BoardGamePlayer::FILTER;
    private const DETAIL_RESOURCE = BoardGamePlayer::DETAIL_RESOURCE;
    private const LIST_RESOURCE = BoardGamePlayer::LIST_RESOURCE;
    private const SERVICE = BoardGamePlayer::SERVICE;

    protected DefaultAdminEntityService $defaultAdminEntityService;

    public function __construct(DefaultAdminEntityService $defaultAdminEntityService)
    {
        $this->defaultAdminEntityService = $defaultAdminEntityService;
    }

    public function store(BgPlayerRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            self::MODEL
        );
    }

    public function update(BgPlayerRequest $request, BoardGamePlayer $boardGamePlayer)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $boardGamePlayer
        );
    }

    public function destroy(BoardGamePlayer $boardGamePlayer)
    {
        return $this->defaultAdminEntityService->destroy($boardGamePlayer);
    }
}
