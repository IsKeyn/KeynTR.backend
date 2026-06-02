<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\NotificationRequest;
use App\Http\Resources\User\NotificationResource;
use App\Models\User\Notification;
use App\Services\Cache\NotificationCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{
    public function set(Notification $notification, NotificationRequest $request)
    {
        $validated = $request->validated();

        $validated['created_by'] = isset($validated['created_by']) ? $validated['created_by'] : $request->user()->id;

        return $notification->create($validated);
    }

    public function setViewed(Notification $notification, Request $request)
    {
        $user = Auth::user();

        $notification = $notification::where('user_id', $user->id)->find($request->id);
        if ($notification) {
            $notification->update(['viewed' => true]);
        }
    }

    public function setViewedAll(Notification $notification, Request $request)
    {
        $user = Auth::user();

        $notification::where('user_id', $user->id)
            ->each(function ($item) {
                $item->update(['viewed' => true]); // события сработают ✅
            });

        return response()->json(['success' => true]);
    }

    public function getCurrentUserNotifications(
        Notification $notification,
        Request $request
    )
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status'         => 'error',
                'status_message' => 'Пользователь не найден или не авторизован',
            ], 401);
        }

        $userId = $user->id;

        $cacheKey = NotificationCacheService::LIST_PREFIX. '_' . $userId . '_' . $request->page . '_' . $request->perPage;

        return Cache::remember($cacheKey, NotificationCacheService::TIME, function () use ($request, $userId, $notification) {
            $list = $notification::where('user_id', $userId)
                ->active()
                ->orderBy('created_at', 'desc')
                ->paginate($request->perPage ? $request->perPage : 10);

            return NotificationResource::collection($list);
        });
    }

    public function getCountUserNotifications(Notification $notification, Request $request)
    {
        $user = Auth::user();

        return $notification::where('user_id', $user->id)->where('viewed', false)->count();
    }
}
