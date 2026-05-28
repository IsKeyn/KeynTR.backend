<?php
namespace App\Http\Controllers\Admin\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Requests\BoardGame\BgLogRequest;
use App\Models\BoardGame\BoardGameLog;
use App\Services\Entity\DefaultAdminEntityService;
use App\Traits\HasBaseAdminFunc;
use Illuminate\Http\JsonResponse;

class BgLogController extends Controller
{
    use HasBaseAdminFunc;

    private const MODEL = BoardGameLog::class;
    private const CACHE_SERVICE = BoardGameLog::CACHE_SERVICE;
    private const FILTER = BoardGameLog::FILTER;
    private const DETAIL_RESOURCE = BoardGameLog::DETAIL_RESOURCE;
    private const LIST_RESOURCE = BoardGameLog::LIST_RESOURCE;
    private const SERVICE = BoardGameLog::SERVICE;

    protected DefaultAdminEntityService $defaultAdminEntityService;

    public function __construct(DefaultAdminEntityService $defaultAdminEntityService)
    {
        $this->defaultAdminEntityService = $defaultAdminEntityService;
    }

    public function store(BgLogRequest $request): JsonResponse
    {
        return $this->defaultAdminEntityService->store(
            $request,
            self::MODEL
        );
    }

    public function update(BgLogRequest $request, BoardGameLog $boardGameLog)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $boardGameLog
        );
    }

    public function destroy(BoardGameLog $boardGameLog)
    {
        return $this->defaultAdminEntityService->destroy($boardGameLog);
    }
}
