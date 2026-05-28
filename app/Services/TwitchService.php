<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class TwitchService
{
    public function getAccessToken($clientId, $clientSecret) {
        $url = 'https://id.twitch.tv/oauth2/token';

        $params = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'client_credentials'
        ];

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query($params)
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        return json_decode($response, true)['access_token'];
    }

    public function getTwitchUserData($username, $clientId, $accessToken) {
        $url = "https://api.twitch.tv/helix/users?login={$username}";

        $options = [
            'http' => [
                'method' => 'GET',
                'header' => [
                    "Client-ID: $clientId",
                    "Authorization: Bearer $accessToken"
                ]
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $data = json_decode($response, true);

        return $data;
    }

    public function isStreamerLive($username, $clientId, $accessToken) {
        $url = "https://api.twitch.tv/helix/streams?user_login={$username}";

        $options = [
            'http' => [
                'method' => 'GET',
                'header' => [
                    "Client-ID: $clientId",
                    "Authorization: Bearer $accessToken"
                ]
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $data = json_decode($response, true);

        return !empty($data['data']); // true — если стрим активен
    }

    public function streamersLive()
    {
        $cacheKey = 'twitch:online';

        return Cache::remember($cacheKey, 300, function () {

            $twitchChannelsList = [];

            $users = User::query()->active()->with('additionalFields')->get();

            foreach ($users as $user) {
                foreach ($user->additionalFields as $field) {
                    if ($field->slug === 'twitch_channel') {
                        $path = parse_url($field->value, PHP_URL_PATH);
                        $twitchChannelsList[$user->id] = basename($path);
                    }
                }
            }

            if (empty($twitchChannelsList)) {
                return [];
            }

            $clientId = config('twitch.client_id');
            $clientSecret = config('twitch.client_secret');

            $token = $this->getAccessToken($clientId, $clientSecret);

            // Разбиваем на чанки по 100 (лимит API)
            $loginsChunks = array_chunk(array_keys($twitchChannelsList), 100);
            $listOnline = [];

            foreach ($loginsChunks as $chunk) {
                $queryParts = [];

                foreach ($chunk as $id) {
                    $queryParts[] = 'user_login=' . rawurlencode($twitchChannelsList[$id]);
                }
                $queryString = implode('&', $queryParts);

                $url = "https://api.twitch.tv/helix/streams?{$queryString}";

                $options = [
                    'http' => [
                        'method' => 'GET',
                        'header' => [
                            "Client-ID: $clientId",
                            "Authorization: Bearer $token"
                        ]
                    ]
                ];

                $context = stream_context_create($options);
                $response = file_get_contents($url, false, $context);
                $data = json_decode($response, true);

                foreach ($data['data'] as $streamer) {
                    $listOnline[] = array_merge($streamer,
                        ['site_user_id' => array_search($streamer['user_login'], $twitchChannelsList)]);
                }
            }

            return $listOnline;
        });
    }
}
