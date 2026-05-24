<?php

namespace App\Http\Controllers\Admin\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Requests\BgPlayerTimerRequest;
use App\Models\BoardGame\BoardGamePlayerTimer;
use App\Services\Entity\DefaultAdminEntityService;
use App\Traits\HasBaseAdminFunc;
use Illuminate\Http\Request;

class BgPlayerTimerController extends Controller
{
    use HasBaseAdminFunc;

    private const MODEL = BoardGamePlayerTimer::class;
    private const CACHE_SERVICE = 'App\Services\Cache\BgPlayerTimerCacheService';
    private const FILTER = 'App\Filters\BgPlayerTimerFilter';
    private const DETAIL_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgPlayerTimer\DetailResource';
    private const LIST_RESOURCE = 'App\Http\Resources\Admin\BoardGame\BgPlayerTimer\ListResource';
    private const SERVICE = 'App\Services\BoardGame\BgTimerTimerService';

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

    public function store(BgPlayerTimerRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            self::MODEL
        );
    }

    public function update(BgPlayerTimerRequest $request, BoardGamePlayerTimer $bgPlayerTimer)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $bgPlayerTimer
        );
    }

    public function destroy(BoardGamePlayerTimer $bgPlayerTimer)
    {
        return $this->defaultAdminEntityService->destroy($bgPlayerTimer);
    }
}
