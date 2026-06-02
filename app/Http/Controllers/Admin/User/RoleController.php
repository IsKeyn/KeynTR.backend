<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Http\Resources\Permission\ShortResource;
use App\Models\Permission;
use App\Models\Role;
use App\Services\Cache\RoleCacheService;
use App\Services\Entity\DefaultAdminEntityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RoleController extends Controller
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
            'App\Models\Role',
            'App\Services\Cache\RoleCacheService',
            'App\Filters\RoleFilter',
            'App\Http\Resources\Admin\Role\ListResource',
        );
    }

    public function store(RoleRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            'App\Models\Role'
        );
    }

    public function update(RoleRequest $request, Role $role)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $role
        );
    }

    public function edit(Request $request, $id)
    {
        return $this->defaultAdminEntityService->edit(
            $request,
            $id,
            'App\Services\RoleService'
        );
    }

    public function destroy(Role $role)
    {
        return $this->defaultAdminEntityService->destroy($role);
    }

    public function forceDelete($id)
    {
        return $this->defaultAdminEntityService->forceDelete(
            'App\Models\Role',
            $id
        );
    }

    public function recovery($id)
    {
        return $this->defaultAdminEntityService->recovery(
            'App\Models\Role',
            $id
        );
    }

    public function getListFilters(Request $request)
    {
        return $this->defaultAdminEntityService->getListFilters(
            $request,
            'App\Models\Role',
            'App\Filters\RoleFilter',
            'App\Services\Cache\RoleCacheService',
        );
    }

    public function getAdditionalData()
    {
        return Cache::remember(RoleCacheService::ADMIN_ADDDATA_PREFIX, RoleCacheService::TIME, function () {
            return [
                'permissions' => ShortResource::collection(Permission::all()),
            ];
        });
    }
}
