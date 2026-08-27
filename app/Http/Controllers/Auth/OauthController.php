<?php

namespace App\Http\Controllers\Auth;

use App\Services\BoardGame\BgPlayerService;
use App\Services\ErrorService;
use App\Services\MediaService;
use App\Services\User\UserPasswordService;
use App\Services\User\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class OauthController extends Controller
{
    public function redirect($oauthName)
    {
        $driver = Socialite::driver($oauthName)->stateless();

        $redirectResponse = $driver->redirect();

        return response()->json([
            'url' => $redirectResponse->getTargetUrl(),
        ]);
    }

    public function apiCallback(Request $request, $oauthName)
    {
        \Log::info('VKID raw debug', [
            'content_type' => $request->header('Content-Type'),
            'all' => $request->all(),
            'query' => $request->query->all(),
            'raw_body' => $request->getContent(),
            '$oauthName' => $oauthName,
        ]);

        if ($oauthName === 'vkid') {
            $request->query->add($request->only([
                'code', 'state', 'device_id', 'expires_in', 'ext_id', 'type',
            ]));
        }

        $driver = Socialite::driver($oauthName)->stateless();
        $provider = $driver->setRequest($request);

        $oauthUser = $provider->user();

        if (!$oauthUser) {
            return ErrorService::message(__('user.not_received', ['name' => $oauthName]));
        }

        if (!$oauthUser->getEmail()) {
            return ErrorService::message(__('user.not_found_email', ['name' => $oauthName]));
        }

        // Ищем пользователя с email, верефицированным email
        $user = User::query()
            ->where('email', $oauthUser->getEmail())
            ->first();

        // Регистрируем пользователя, если его нет в системе
        if (!$user) {
            $name = $oauthUser->getNickname();

            if (!$name) {
                $name = $oauthUser->getName();
            }

            // Проверка никнейма и добаляем текст, если такой никнейм уже существует на сайте
            $userLogin = UserService::checkLogin($name);

            // Генерируем пароль
            $userPasswordService = new UserPasswordService();

            $newUserFields = [
                'name' => $userLogin,
                'public_name' => $name,
                'email' => $oauthUser->getEmail(),
                'email_verified_at' => Carbon::now(),
                'password' => $userPasswordService->generatePassword(),
            ];

            // Создаем пользователя
            $user = User::create($newUserFields);

            // Добавляем пользователю аватар
            if ($oauthUser->getAvatar()) {
                $response = Http::get($oauthUser->getAvatar());
                $imageContent = $response->body();

                // Создаем временный файл
                $tempFile = tempnam(sys_get_temp_dir(), 'downloaded_image');
                file_put_contents($tempFile, $imageContent);

                $contentType = $response->header('Content-Type') ?: 'image/jpeg';

                // Создаем объект UploadedFile
                $uploadedFile = new \Illuminate\Http\UploadedFile(
                    $tempFile,
                    $userLogin . '_avatar',
                    $response->header('Content-Type', 'image/jpeg'),
                    null,
                    true
                );

                $fileArray = [
                    'name' => 'Аватар пользователя с никнеймом ' . $userLogin,
                    'src' => $uploadedFile,
                ];

                $mediaService = new MediaService();

                if ($avatar = $mediaService->addMedia($fileArray, $user)) {
                    $user->avatar()->syncWithPivotValues($avatar->id, ['type' => 1]);
                }
            }

            $additionalFields = [];

            // Добавляем пользователю дополнительные поля
            if ($oauthName === 'twitch') {
                $additionalFields[] = [
                    'name' => 'Twitch канал',
                    'slug' => 'twitch_channel',
                    'value' => $oauthUser->getNickname(),
                    'sort' => 2,
                ];
            }

            $additionalFields[] = [
                'name' => "{$oauthName} id",
                'slug' => "{$oauthName}_id",
                'value' => $oauthUser->getId(),
                'sort' => 5,
            ];

            if ($additionalFields) {
                UserService::setAdditionalFields($user, $additionalFields);
            }
        }

        if (!$user->email_verified_at) {
            return ErrorService::message(__('user.confirm_you_email'));
        }

        Auth::login($user);

        if ($request->registerOnEventBySlug) {
            BgPlayerService::joinTheGame($user, $request->registerOnEventBySlug);
        }

        return Auth::user();
    }
}
