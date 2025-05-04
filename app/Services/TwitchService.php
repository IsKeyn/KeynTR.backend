<?php

namespace App\Services;

use App\Models\Link;

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

}
