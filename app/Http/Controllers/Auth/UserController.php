<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function authUser(Request $request) {
        $user = $request->user();

        return UserResource::make($user);
    }

    public function sendVerificationNotification(Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return [
            'status_code' => 'notifications.account_verification',
            'status' => __('notifications.account_verification'),
        ];
    }

    public function setAvatar(Request $request) {
        $user = $request->user();

        $mediaService = new MediaService();

        if (count($user->avatar) > 0) {
            $media = Media::where('id', $user->avatar->first()->id)->first();
            $mediaService->destroy($media);
            $user->avatar()->wherePivot('type', '=', 1)->detach();
        }

        $fileArray = [
            'name' => 'Аватар пользователя с никнеймом ' . $user->name,
            'src' => $request->file('avatar'),
        ];

        if ($avatar = $mediaService->addMedia($fileArray, $user)) {
            $user->avatar()->syncWithPivotValues($avatar->id, ['type' => 1]);
        }

        return $avatar;
    }
}
