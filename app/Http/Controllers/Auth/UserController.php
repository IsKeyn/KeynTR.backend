<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Media;
use App\Services\AdditionalFieldsService;
use App\Services\MediaService;
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
            $additionalFieldsService = new AdditionalFieldsService();
            $additionalFieldsService->sync($model, $validated['additional_fields']);
        }
    }
}
