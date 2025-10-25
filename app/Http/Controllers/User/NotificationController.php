<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\NotificationResource;
use App\Models\User\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function set(Notification $notification, Request $request)
    {
        $validated = $this->validateFields($request);

        $validated['created_by'] = isset($validated['created_by']) ? $validated['created_by'] : $request->user()->id;

        return $notification->create($validated);
    }

    public function setViewed(Notification $notification, Request $request)
    {
        $user = Auth::user();

        $notification = $notification::where('user_id', $user->id)->where('id', $request->id);
        return $notification->update(['viewed' => true]);
    }

    public function setViewedAll(Notification $notification, Request $request)
    {
        $user = Auth::user();

        $notifications = $notification::where('user_id', $user->id);
        return $notifications->update(['viewed' => true]);
    }

    public function getCurrentUserNotifications(Notification $notification, Request $request)
    {
        $user = Auth::user();

        return NotificationResource::collection($notification::where('user_id', $user->id)->orderBy('created_at', 'desc')->get());
    }

    public function getCountUserNotifications(Notification $notification, Request $request)
    {
        $user = Auth::user();

        return $notification::where('user_id', $user->id)->where('viewed', false)->count();
    }

    public function validateFields($request) {
        return $request->validate([
            'user_id' => 'required|integer',
            'message' => 'required|string',
            'viewed' => 'sometimes',
            'created_by' => 'sometimes',
            'created_at' => 'sometimes',
        ]);
    }
}
