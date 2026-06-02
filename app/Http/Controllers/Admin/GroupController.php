<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\groupRequest;
use App\Models\Group;
use App\Services\Entity\DefaultAdminEntityService;
use Illuminate\Http\Request;

class GroupController extends Controller
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
            'App\Models\Group',
            'App\Services\Cache\GroupCacheService',
            'App\Filters\GroupFilter',
            'App\Http\Resources\Admin\Group\ListResource',
        );
    }

    public function store(groupRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            'App\Models\Group'
        );
    }

    public function update(groupRequest $request, Group $group)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $group
        );
    }

    public function edit(Request $request, $id)
    {
        return $this->defaultAdminEntityService->edit(
            $request,
            $id,
            'App\Services\GroupService'
        );
    }

    public function destroy(Group $group)
    {
        return $this->defaultAdminEntityService->destroy($group);
    }

    public function forceDelete($id)
    {
        return $this->defaultAdminEntityService->forceDelete(
            'App\Models\Group',
            $id
        );
    }

    public function recovery($id)
    {
        return $this->defaultAdminEntityService->recovery(
            'App\Models\Group',
            $id
        );
    }

    public function getListFilters(Request $request)
    {
        return $this->defaultAdminEntityService->getListFilters(
            $request,
            'App\Models\Group',
            'App\Filters\GroupFilter',
            'App\Services\Cache\GroupCacheService',
        );
    }
}
