<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

use App\Http\Resources\User\UserPublicResource;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * Получаем список веринтифицированных пользователей
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function list()
    {
        $userList = User::query()
            ->verified()
            ->with([
                'avatar',
                'additionalFields',
            ])
            ->get();

        return UserPublicResource::collection($userList);
    }

    /**
     * Получаем список пользователь по их ID
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getById(Request $request)
    {
        if (!$request->userIds) {
            abort(Response::HTTP_BAD_REQUEST, __('user.not_received_user_ids'));
        }

        $userIds = $request->userIds;

        if (is_string($userIds)) {
            $userIds = explode(',', $userIds);
        }

        $userList = User::query()
            ->whereIn('id', $userIds)
            ->with([
                'avatar',
            ])
            ->get();

        return UserPublicResource::collection($userList);
    }
}
