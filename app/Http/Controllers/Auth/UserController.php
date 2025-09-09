<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Media;
use App\Models\User;
use App\Services\AdditionalFieldsService;
use App\Services\MediaService;
use App\Services\TwitchService;
use App\Services\User\UserPasswordService;
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
            foreach ($validated['additional_fields'] as &$field) {
                if ($field['slug'] === 'twitch_channel') {
                    $path = parse_url($field['value'], PHP_URL_PATH);
                    $twitchName = basename($path);

                    $field['value'] = $twitchName;

                    $clientId = config('twitch.client_id');
                    $clientSecret = config('twitch.client_secret');

                    $twitchService = new TwitchService();
                    $token = $twitchService->getAccessToken($clientId, $clientSecret);

                    $twitchUserData = $twitchService->getTwitchUserData($twitchName, $clientId, $token);

                    if (isset($twitchUserData['data'][0])) {
                        $validated['additional_fields'][] = [
                            'name' => 'Twitch ID',
                            'slug' => 'twitch_id',
                            'value' => $twitchUserData['data'][0]['id'],
                            'sort' => 90,
                        ];
                    }
                }
            }

            $additionalFieldsService = new AdditionalFieldsService();
            $additionalFieldsService->sync($model, $validated['additional_fields']);
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
}
