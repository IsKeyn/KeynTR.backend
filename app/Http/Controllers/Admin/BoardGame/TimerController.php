<?php

namespace App\Http\Controllers\Admin\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Requests\TimerRequest;
use App\Models\BoardGame\Timer;
use App\Services\Entity\DefaultAdminEntityService;
use App\Traits\HasBaseAdminFunc;
use Illuminate\Http\Request;

class TimerController extends Controller
{
    use HasBaseAdminFunc;

    private const MODEL = Timer::class;
    private const CACHE_SERVICE = 'App\Services\Cache\TimerCacheService';
    private const FILTER = 'App\Filters\TimerFilter';
    private const DETAIL_RESOURCE = 'App\Http\Resources\Admin\BoardGame\Timer\DetailResource';
    private const LIST_RESOURCE = 'App\Http\Resources\Admin\BoardGame\Timer\ListResource';
    private const SERVICE = 'App\Services\BoardGame\TimerService';

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

    public function store(TimerRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            self::MODEL
        );
    }

    public function update(TimerRequest $request, Timer $timer)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $timer
        );
    }

    public function destroy(Timer $timer)
    {
        return $this->defaultAdminEntityService->destroy($timer);
    }
}
