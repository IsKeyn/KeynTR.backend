<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SettingRequest;
use App\Models\Setting;
use App\Services\Entity\DefaultAdminEntityService;
use Illuminate\Http\Request;

class SettingController extends Controller
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
            'App\Models\Setting',
            'App\Services\Cache\SettingCacheService',
            'App\Filters\SettingFilter',
            'App\Http\Resources\Admin\Setting\ListResource',
        );
    }

    public function store(SettingRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            'App\Models\Setting'
        );
    }

    public function update(SettingRequest $request, Setting $setting)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $setting
        );
    }

    public function edit(Request $request, $id)
    {
        return $this->defaultAdminEntityService->edit(
            $request,
            $id,
            'App\Services\SettingService'
        );
    }

    public function destroy(Setting $setting)
    {
        return $this->defaultAdminEntityService->destroy($setting);
    }

    public function forceDelete($id)
    {
        return $this->defaultAdminEntityService->forceDelete(
            'App\Models\Setting',
            $id
        );
    }

    public function recovery($id)
    {
        return $this->defaultAdminEntityService->recovery(
            'App\Models\Setting',
            $id
        );
    }

    public function getListFilters(Request $request)
    {
        return $this->defaultAdminEntityService->getListFilters(
            $request,
            'App\Models\Setting',
            'App\Filters\SettingFilter',
            'App\Services\Cache\SettingCacheService',
        );
    }
}
