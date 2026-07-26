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

class YandexAuthController extends Controller
{
    // 1. Редирект на Яндекс
    public function redirect()
    {
        $redirectResponse = Socialite::driver('yandex')->stateless()->redirect();
        $redirectUrl = $redirectResponse->getTargetUrl();

        return response()->json(['url' => $redirectUrl]);
    }

    // 2. Обработка ответа от Яндекса
    public function apiCallback(Request $request)
    {
        $provider = Socialite::driver('yandex')
            ->stateless()
            ->setRequest($request);

        $yandexUser = $provider->user();

        if (!$yandexUser) {
            return ErrorService::message('Не получен пользователь от Yandex');
        }

        if (!$yandexUser->getEmail()) {
            return ErrorService::message('У Yandex пользователя отсутсвует email');
        }

        // Ищем пользователя с таким же email как и в yandex и с верентифицированным email
        $user = User::query()
            ->where('email', $yandexUser->getEmail())
            ->first();

        // Регистрируем пользователя, если его нет в системе
        if (!$user) {
            // Проверка никнейма и добаляем текст, если такой никнейм уже существует на сайте
            $userLogin = UserService::checkLogin($yandexUser->getNickname());

            // Генерируем пароль
            $userPasswordService = new UserPasswordService();

            $newUserFields = [
                'name' => $userLogin,
                'public_name' => $yandexUser->getNickname(),
                'email' => $yandexUser->getEmail(),
                'email_verified_at' => Carbon::now(),
                'password' => $userPasswordService->generatePassword(),
            ];

            // Создаем пользователя
            $user = User::create($newUserFields);

            // Добавляем пользователю аватар
            if ($yandexUser->getAvatar()) {
                $response = Http::get($yandexUser->getAvatar());
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
                    'name' => 'Yandex id',
                    'slug' => 'yandex_id',
                    'value' => $yandexUser->getId(),
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
