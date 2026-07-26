<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BoardGame\BgPlayerService;
use App\Services\ErrorService;
use App\Services\MediaService;
use App\Services\User\UserPasswordService;
use App\Services\User\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;

class VkAuthController extends Controller
{
    // 1. Редирект на Яндекс
    public function redirect()
    {
        $redirectResponse = Socialite::driver('vkontakte')->stateless()->redirect();
        $redirectUrl = $redirectResponse->getTargetUrl();

        return response()->json(['url' => $redirectUrl]);
    }

    // 2. Обработка ответа от Яндекса
    public function apiCallback(Request $request)
    {
        $provider = Socialite::driver('vkontakte')
            ->stateless()
            ->setRequest($request);

        $oauthUser = $provider->user();

        if (!$oauthUser) {
            return ErrorService::message('Не получен пользователь от Yandex');
        }

        if (!$oauthUser->getEmail()) {
            return ErrorService::message('У Yandex пользователя отсутсвует email');
        }

        // Ищем пользователя с таким же email как и в yandex и с верентифицированным email
        $user = User::query()
            ->where('email', $oauthUser->getEmail())
            ->first();

        // Регистрируем пользователя, если его нет в системе
        if (!$user) {
            // Проверка никнейма и добаляем текст, если такой никнейм уже существует на сайте
            $userLogin = UserService::checkLogin($oauthUser->getName() ?? 'VK User',);

            // Генерируем пароль
            $userPasswordService = new UserPasswordService();

            $newUserFields = [
                'name' => $userLogin,
                'public_name' => $oauthUser->getName(),
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
                    'name' => 'VK id',
                    'slug' => 'vk_id',
                    'value' => $oauthUser->getId(),
                    'sort' => 4,
                ],
            ];

            UserService::setAdditionalFields($user, $additionalFields);
        }

        if ($user->email_verified_at) {
            Auth::login($user);

            if ($request->registerOnEventBySlug) {
                BgPlayerService::joinTheGame($user, $request->registerOnEventBySlug);
            }

            return Auth::user();
        } else {
            return ErrorService::message('Для авторизации этим пользователем, вы должны подтвердить свой email');
        }
    }
}
