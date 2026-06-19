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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class UserController extends Controller
{
    public function authUser(Request $request) {
        return UserService::getAuthUser($request);
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

            if (isset($validated['public_name'])) {
                $user->public_name = $validated['public_name'];
                $user->save();
            }
        }

        return $user;
    }

    public function validateFields($request) {
        return $request->validate([
            'name' => 'sometimes|string',
            'public_name' => 'sometimes|string',
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
            'rouletteSetting' => 'sometimes',
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

    public function generateAuthLink(Request $request)
    {
        $user = Auth::user();

        $redirectUrl = $request->redirectUrl ?? null;

        $link = MagicLinkService::createLink($user->id, $redirectUrl);

        if (isset($link['error'])) {
            return $link['error'];
        }

        return response()->json([
            'token' => $link->token,
            'expires_at' => $link->expires_at,
            'qr_code' => $link->qr_code,
        ]);
    }

    public function fullLogout(Request $request)
    {
        $user = $request->user();

        // Удалить текущий токен (обычно используется при logout)
        $user->currentAccessToken()->delete();

        // Удалить все токены пользователя
        $user->tokens()->delete();
    }

    public function getSanctumToken(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status'         => 'error',
                'status_message' => 'Пользователь не найден или не авторизован',
            ], 401);
        }

        $tokenName = $request->name ?: config('app.name') . '_token';

        // Удаляем все существующие токены пользователя с таким же именем
        $user->tokens()->where('name', $tokenName)->delete();

        $newToken = $user->createToken($tokenName);
        $plainToken = $newToken->plainTextToken;

        $accessToken = $newToken->accessToken; // Это экземпляр PersonalAccessToken
        $accessToken->expires_at = Carbon::now()->addHours(24);
        $accessToken->save();

        return response()->json([
            'token' => $plainToken,
            'expires_at' => $accessToken->expires_at,
        ]);
    }

    public function verifyToken(Request $request)
    {
        $plainToken = $request->input('token');

        if (!$plainToken) {
            return response()->json([
                'valid'   => false,
                'message' => 'Токен не передан'
            ], 400);
        }

        // 1. Ищем токен в БД (Sanctum автоматически применяет хеширование)
        $token = PersonalAccessToken::findToken($plainToken);

        if (!$token) {
            return response()->json([
                'valid'   => false,
                'message' => 'Токен не найден или некорректен'
            ], 404);
        }

        // 2. Проверяем срок действия (expires_at кастится к Carbon автоматически)
        if ($token->expires_at && now()->greaterThan($token->expires_at)) {
            // Опционально: сразу удаляем истекший токен из БД
            $token->delete();

            return response()->json([
                'valid'   => false,
                'message' => 'Срок действия токена истёк'
            ], 401);
        }

        // 3. Токен валиден
        return response()->json([
            'valid'      => true,
            'message'    => 'Токен действителен',
            'user_id'    => $token->tokenable_id,
            'user_type'  => $token->tokenable_type,
            'expires_at' => $token->expires_at?->toISOString(),
        ]);
    }
}
