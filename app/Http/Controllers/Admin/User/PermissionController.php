<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\PermissionRequest;
use App\Models\Permission;
use App\Services\Entity\DefaultAdminEntityService;
use Illuminate\Http\Request;

class PermissionController extends Controller
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
            'App\Models\Permission',
            'App\Services\Cache\PermissionCacheService',
            'App\Filters\PermissionFilter',
            'App\Http\Resources\Admin\Permission\ListResource',
        );
    }


    public function store(PermissionRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            'App\Models\Permission'
        );
    }

    public function update(PermissionRequest $request, Permission $permission)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $permission
        );
    }

    public function edit(Request $request, $id)
    {
        return $this->defaultAdminEntityService->edit(
            $request,
            $id,
            'App\Services\PermissionService'
        );
    }

    public function destroy(Permission $permission)
    {
        return $this->defaultAdminEntityService->destroy($permission);
    }

    public function forceDelete($id)
    {
        return $this->defaultAdminEntityService->forceDelete(
            'App\Models\Permission',
            $id
        );
    }

    public function recovery($id)
    {
        return $this->defaultAdminEntityService->recovery(
            'App\Models\Permission',
            $id
        );
    }

    public function getListFilters(Request $request)
    {
        return $this->defaultAdminEntityService->getListFilters(
            $request,
            'App\Models\Permission',
            'App\Filters\PermissionFilter',
            'App\Services\Cache\PermissionCacheService',
        );
    }
}
