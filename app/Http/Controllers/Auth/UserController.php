<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Media;
use App\Models\User;
use App\Services\MediaService;
use App\Services\User\MagicLinkService;
use App\Services\User\UserPasswordService;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function updateProfile(Request $request) {
        $user = Auth::user();

        $validated = $this->validateFields($request);

        if ($user) {
            $this->setAdditionalFields($user, $validated);
        }

        return $user;
    }

    public function validateFields($request) {
        return $request->validate([
            'name' => 'sometimes|string',
            'additional_fields' => 'sometimes',
        ]);
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

    public function setAdditionalFields($model, $validated) {
        if (isset($validated['additional_fields'])) {
            UserService::setAdditionalFields($model, $validated['additional_fields']);
        }
    }

    public function setSettings(Request $request): UserResource
    {
        $validated = $request->validate([
            'theme' => 'sometimes',
            'soundVolume' => 'sometimes|numeric',
        ]);

        $user = Auth::user();

        if ($user) {
            $settings = $user->settings;

            foreach ($validated as $settingName => $settingValue) {
                $settings[$settingName] = $settingValue;
            }

            $user->settings = $settings;
            $user->save();

            return UserResource::make($user);
        }
    }

    public function getFullProfile($name, Request $request): UserResource
    {
        $user = User::where('name', $name)->first();

        return UserResource::make($user);
    }

    public function changePassword(Request $request): array
    {
        $validated = $request->validate([
            'currentPassword' => 'sometimes|required|string|min:8',
            'password' => 'sometimes|required|string|min:8|confirmed',
            'password_confirmation' => 'sometimes|required|string|min:8',
        ]);

        $user = Auth::user();

        $userPasswordService = new UserPasswordService();

       return $userPasswordService->changePassword($user, $validated);
    }

    public function generateAuthLink()
    {
        $user = Auth::user();

        $link = MagicLinkService::createLink($user->id);

        if (isset($link['error'])) {
            return $link['error'];
        }

        return response()->json([
            'token' => $link->token,
            'expires_at' => $link->expires_at,
            'qr_code' => $link->qr_code,
        ]);
    }
}
