<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Entity\DefaultAdminEntityService;
use Illuminate\Http\Request;
use App\Http\Requests\GamingPlatformRequest;
use App\Models\GamingPlatform;

class AdminGamingPlatformController extends Controller {
    protected DefaultAdminEntityService $defaultAdminEntityService;

    public function __construct(DefaultAdminEntityService $defaultAdminEntityService)
    {
        $this->defaultAdminEntityService = $defaultAdminEntityService;
    }

    public function index(Request $request)
    {
        return $this->defaultAdminEntityService->index(
            $request,
            'App\Models\GamingPlatform',
            'App\Services\Cache\GamingPlatformCacheService',
            'App\Filters\GamingPlatformFilter',
            'App\Http\Resources\Admin\GamingPlatform\ListResource',
        );
    }

    public function store(GamingPlatformRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            'App\Models\GamingPlatform'
        );
    }

    public function update(GamingPlatformRequest $request, GamingPlatform $gamingPlatform)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $gamingPlatform
        );
    }

    public function edit(Request $request, $id)
    {
        return $this->defaultAdminEntityService->edit(
            $request,
            $id,
            'App\Services\GamingPlatformService'
        );
    }

    public function destroy(GamingPlatform $gamingPlatform)
    {
        return $this->defaultAdminEntityService->destroy($gamingPlatform);
    }

    public function forceDelete($id)
    {
        return $this->defaultAdminEntityService->forceDelete(
            'App\Models\GamingPlatform',
            $id
        );
    }

    public function recovery($id)
    {
        return $this->defaultAdminEntityService->recovery(
            'App\Models\GamingPlatform',
            $id
        );
    }
}
