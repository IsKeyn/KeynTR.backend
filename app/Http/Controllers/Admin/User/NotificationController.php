<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\NotificationRequest;
use App\Models\User\Notification;
use App\Services\Entity\DefaultAdminEntityService;
use Illuminate\Http\Request;

class NotificationController extends Controller
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
            'App\Models\User\Notification',
            'App\Services\Cache\NotificationCacheService',
            'App\Filters\NotificationFilter',
            'App\Http\Resources\Admin\Notification\ListResource',
        );
    }

    public function store(NotificationRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            'App\Models\User\Notification'
        );
    }

    public function update(NotificationRequest $request, Notification $notification)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $notification
        );
    }

    public function edit(Request $request, $id)
    {
        return $this->defaultAdminEntityService->edit(
            $request,
            $id,
            'App\Services\NotificationService'
        );
    }

    public function destroy(Notification $notification)
    {
        return $this->defaultAdminEntityService->destroy($notification);
    }

    public function forceDelete($id)
    {
        return $this->defaultAdminEntityService->forceDelete(
            'App\Models\User\Notification',
            $id
        );
    }

    public function recovery($id)
    {
        return $this->defaultAdminEntityService->recovery(
            'App\Models\User\Notification',
            $id
        );
    }

    public function getListFilters(Request $request)
    {
        return $this->defaultAdminEntityService->getListFilters(
            $request,
            'App\Models\User\Notification',
            'App\Filters\NotificationFilter',
            'App\Services\Cache\NotificationCacheService',
        );
    }
}
