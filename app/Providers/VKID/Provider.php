<?php

declare(strict_types=1);

namespace App\Providers\VKID;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Socialite\Two\InvalidStateException;
use League\OAuth2\Client\Token\AccessToken;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    public const IDENTIFIER = 'VKID';

    protected $scopes = [
        'vkid.personal_info',
        'email',
    ];

    /**
     * VK ID authorization endpoint.
     */
    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase(
            'https://id.vk.ru/authorize',
            $state
        );
    }

    /**
     * VK ID token endpoint.
     */
    protected function getTokenUrl(): string
    {
        return 'https://id.vk.ru/oauth2/auth';
    }

    /**
     * Формируем authorization URL + PKCE.
     */
    protected function getCodeFields($state = null): array
    {
        $state = (string) $state;

        $verifier = Str::random(64);

        $challenge = rtrim(
            strtr(
                base64_encode(hash('sha256', $verifier, true)),
                '+/',
                '-_'
            ),
            '='
        );

        $ttl = (int) config('services.vkid.pkce_ttl', 10);

        $cacheKey = $this->getPkceCacheKey($state);

        Cache::put(
            $cacheKey,
            $verifier,
            now()->addMinutes($ttl)
        );

        return [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUrl,
            'scope' => implode(' ', $this->getScopes()),
            'response_type' => 'code',
            'state' => $state,

            'code_challenge_method' => 'S256',
            'code_challenge' => $challenge,
        ];
    }

    /**
     * Обмен authorization code на access_token.
     */
    protected function getTokenFields($code): array
    {
        $state = request()->query('state');

        if (!$state) {
            throw new InvalidStateException(
                'VK ID: missing state.'
            );
        }

        $cacheKey = $this->getPkceCacheKey((string) $state);

        $verifier = Cache::pull($cacheKey);

        if (!$verifier) {
            throw new InvalidStateException(
                'VK ID: PKCE verifier expired or not found.'
            );
        }

        $fields = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,

            'redirect_uri' => $this->redirectUrl,

            'code' => (string) $code,

            'code_verifier' => $verifier,
        ];

        /*
         * VK ID передаёт device_id в callback.
         */
        $deviceId = request()->query('device_id');

        if ($deviceId) {
            $fields['device_id'] = (string) $deviceId;
        }

        return $fields;
    }

    /**
     * VK ID token endpoint.
     *
     * VK ID возвращает JSON:
     *
     * {
     *   access_token,
     *   refresh_token,
     *   id_token,
     *   user_id,
     *   scope,
     *   expires_in
     * }
     */
    public function getAccessTokenResponse($code): array
    {
        $response = Http::asForm()
            ->acceptJson()
            ->timeout(15)
            ->post(
                $this->getTokenUrl(),
                $this->getTokenFields($code)
            );

        if (!$response->successful()) {
            throw new \RuntimeException(
                'VK ID token request failed: ' .
                $response->status() .
                ' ' .
                $response->body()
            );
        }

        $data = $response->json();

        if (!is_array($data)) {
            throw new \RuntimeException(
                'VK ID token response is not valid JSON.'
            );
        }

        if (empty($data['access_token'])) {
            throw new \RuntimeException(
                'VK ID token response does not contain access_token: ' .
                json_encode($data, JSON_UNESCAPED_UNICODE)
            );
        }

        return $data;
    }

    /**
     * Получаем пользователя через официальный VK ID endpoint.
     *
     * https://id.vk.ru/oauth2/user_info
     */
    protected function getUserByToken($token): array
    {
        $accessToken = $token instanceof AccessToken
            ? $token->getToken()
            : (string) $token;

        $response = Http::asForm()
            ->acceptJson()
            ->timeout(15)
            ->post(
                'https://id.vk.ru/oauth2/user_info',
                [
                    'access_token' => $accessToken,
                    'client_id' => $this->clientId,
                ]
            );

        if (!$response->successful()) {
            throw new \RuntimeException(
                'VK ID user_info request failed: ' .
                $response->status() .
                ' ' .
                $response->body()
            );
        }

        $data = $response->json();

        if (!is_array($data)) {
            throw new \RuntimeException(
                'VK ID user_info response is not valid JSON.'
            );
        }

        /*
         * Очень важный момент:
         *
         * user_info может содержать данные непосредственно
         * в корне ответа.
         *
         * Поэтому не делаем жёсткую привязку только к
         * $data['user'].
         */

        if (isset($data['user']) && is_array($data['user'])) {
            $user = $data['user'];
        } else {
            $user = $data;
        }

        /*
         * Нормализуем поля под Socialite.
         */

        $id = $user['user_id']
            ?? $user['id']
            ?? null;

        $firstName = $user['first_name']
            ?? null;

        $lastName = $user['last_name']
            ?? null;

        $name = $user['name']
            ?? trim(
                ($firstName ?? '') .
                ' ' .
                ($lastName ?? '')
            );

        $nickname = $user['screen_name']
            ?? $user['nickname']
            ?? $user['domain']
            ?? null;

        $avatar = $user['avatar']
            ?? $user['photo_200']
            ?? $user['photo']
            ?? null;

        $email = $user['email']
            ?? null;

        $phone = $user['phone']
            ?? null;

        return [
            'id' => $id,

            'nickname' => $nickname,

            'name' => $name,

            'email' => $email,

            'avatar' => $avatar,

            'first_name' => $firstName,

            'last_name' => $lastName,

            'phone' => $phone,

            /*
             * Сохраняем полный ответ VK.
             */
            '_vk_raw' => $data,
        ];
    }

    /**
     * Преобразуем VK ID user_info
     * в Socialite User.
     */
    protected function mapUserToObject(array $user)
    {
        return (new User())
            ->setRaw($user)
            ->map([
                'id' => $user['id'] ?? null,
                'nickname' => $user['nickname'] ?? null,
                'name' => $user['name'] ?? null,
                'email' => $user['email'] ?? null,
                'avatar' => $user['avatar'] ?? null,
                'phone' => $user['phone'] ?? null,
            ]);
    }

    /**
     * Cache key для PKCE verifier.
     */
    protected function getPkceCacheKey(string $state): string
    {
        return 'socialite:vkid:pkce:' . hash(
                'sha256',
                $state
            );
    }
}
