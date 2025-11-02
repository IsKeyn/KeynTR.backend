<?php

namespace App\Http\Controllers\Auth;

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

class TwitchController extends Controller
{
    public function redirect()
    {
        $redirectResponse = Socialite::driver('twitch')->stateless()->redirect();
        $redirectUrl = $redirectResponse->getTargetUrl();

        return response()->json(['url' => $redirectUrl]);
    }

    public function apiCallback(Request $request)
    {
        $provider = Socialite::driver('twitch')
            ->stateless()
            ->setRequest($request);

        $twitchUser = $provider->user();

        if (!$twitchUser) {
            return ErrorService::message('Не получен пользователь от Twitch');
        }

        if (!$twitchUser->getEmail()) {
            return ErrorService::message('У twitch пользователя отсутсвует email');
        }

        // Ищем пользователя с таким же email как и на twitch и с верентифицированным email
        $user = User::query()
            ->where('email', $twitchUser->getEmail())
            ->first();

        // Регистрируем пользователя, если его нет в системе
        if (!$user) {
            // Проверка никнейма и добаляем текст, если такой никнейм уже существует на сайте
            $userLogin = UserService::checkLogin($twitchUser->getNickname());

            // Генерируем пароль
            $userPasswordService = new UserPasswordService();

            $newUserFields = [
                'name' => $userLogin,
                'public_name' => $twitchUser->getNickname(),
                'email' => $twitchUser->getEmail(),
                'email_verified_at' => Carbon::now(),
                'password' => $userPasswordService->generatePassword(),
            ];

            // Создаем пользователя
            $user = User::create($newUserFields);

            // Добавляем пользователю аватар
            if ($twitchUser->getAvatar()) {
                $response = Http::get($twitchUser->getAvatar());
                $imageContent = $response->body();

                // Создаем временный файл
                $tempFile = tempnam(sys_get_temp_dir(), 'downloaded_image');
                file_put_contents($tempFile, $imageContent);

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

            // Добавляем пользователю дополнительные поля
            $additionalFields = [
                [
                    'name' => 'Twitch канал',
                    'slug' => 'twitch_channel',
                    'value' => $twitchUser->getNickname(),
                    'sort' => 2,
                ],
            ];

            UserService::setAdditionalFields($user, $additionalFields);
        }

        if ($user->email_verified_at) {
            Auth::login($user);
            return Auth::user();
        } else {
            return ErrorService::message('Для авторизации этим пользователем, вы должны подтвердить свой email');
        }
    }
}
