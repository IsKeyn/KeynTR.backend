<?php

namespace App\Services\User;

use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Services\AdditionalFieldsService;
use App\Services\Cache\UserCacheService;
use App\Services\Entity\EntityService;
use App\Services\TwitchService;
use Illuminate\Support\Facades\Cache;

class UserService
{
    public static function getAuthUser($request)
    {
        if (!$request->user()) {
            return null;
        }

        $id = $request->user()->id;

        $cacheKey = UserCacheService::DETAIL_PREFIX . '_' . $id;

        return Cache::remember($cacheKey, UserCacheService::TIME, function () use ($id) {
            $user = User::findById($id)
                ->active()
                ->with(
                    'avatar',
                    'roles',
                    'roles.permissions',
                    'additionalFields'
                )
                ->first();

            return UserResource::make($user);
        });
    }

    public static function verifyEmail($request)
    {
        $request->fulfill();
        $user = $request->user();

        return UserResource::make($user);
    }

    public static function setAdditionalFields($model, $additionalFields)
    {
        if (isset($additionalFields)) {
            foreach ($additionalFields as &$field) {
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
                        $additionalFields[] = [
                            'name' => 'Twitch ID',
                            'slug' => 'twitch_id',
                            'value' => $twitchUserData['data'][0]['id'],
                            'sort' => 90,
                        ];
                    }
                }
            }

            $additionalFieldsService = new AdditionalFieldsService();
            $additionalFieldsService->sync($model, $additionalFields);
        }
    }

    public static function checkLogin($login)
    {
        $user = User::query()->where('name', $login)->first();

        if ($user) {
            // Придумываем новый логин
            $number = 0;

            if (preg_match('/^(.*)_n(\d+)$/', $login, $matches)) {
                if (isset($matches[1])) {
                    $login = $matches[1];
                }

                if (isset($matches[1])) {
                    $number = $matches[2];
                }
            }

            $newLogin = $login . '_n' . ++$number;

            return self::checkLogin($newLogin);
        } else {
            return $login;
        }
    }

    public static function getById($id, $forceRefresh = false, $withTrashed = false)
    {
        return EntityService::getById(
            'App\Models\User',
            'App\Services\Cache\UserCacheService',
            'App\Http\Resources\Admin\User\DetailResource',
            $id,
            ['tags', 'additionalFields', 'roles', 'avatar'],
            $forceRefresh,
            $withTrashed,
        );
    }
}
