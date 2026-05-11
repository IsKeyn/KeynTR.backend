<?php

namespace App\Http\Controllers\Admin\Menu;

use App\Http\Controllers\Controller;
use App\Http\Requests\MenuRequest;
use App\Http\Resources\Permission\ShortResource;
use App\Models\Permission;
use App\Models\Menu;
use App\Services\Cache\MenuCacheService;
use App\Services\Entity\DefaultAdminEntityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MenuController extends Controller
{
    protected DefaultAdminEntityService $defaultAdminEntityService;

    public function __construct(DefaultAdminEntityService $defaultAdminEntityService)
    {
        $this->defaultAdminEntityService = $defaultAdminEntityService;
    }

    public function index(Request $request)
    {
        return $this->defaultAdminEntityService->index(
            $request,
            'App\Models\Menu',
            'App\Services\Cache\MenuCacheService',
            'App\Filters\MenuFilter',
            'App\Http\Resources\Admin\Menu\ListResource',
        );
    }

    public function store(MenuRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            'App\Models\Menu'
        );
    }

    public function update(MenuRequest $request, Menu $menu)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $menu
        );
    }

    public function edit(Request $request, $id)
    {
        return $this->defaultAdminEntityService->edit(
            $request,
            $id,
            'App\Services\MenuService'
        );
    }

    public function destroy(Menu $menu)
    {
        return $this->defaultAdminEntityService->destroy($menu);
    }

    public function forceDelete($id)
    {
        return $this->defaultAdminEntityService->forceDelete(
            'App\Models\Menu',
            $id
        );
    }

    public function recovery($id)
    {
        return $this->defaultAdminEntityService->recovery(
            'App\Models\Menu',
            $id
        );
    }

    public function getListFilters(Request $request)
    {
        return $this->defaultAdminEntityService->getListFilters(
            $request,
            'App\Models\Menu',
            'App\Filters\MenuFilter',
            'App\Services\Cache\MenuCacheService',
        );
    }

    public function getAdditionalData()
    {
        return Cache::remember(MenuCacheService::ADMIN_ADDDATA_PREFIX, MenuCacheService::TIME, function () {
            return [
                'permissions' => ShortResource::collection(Permission::all()),
            ];
        });
    }
}
