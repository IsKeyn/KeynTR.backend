<?php

namespace App\Services\Speedruncom;

class SpeedrunApiService
{
    const API_URL = 'https://www.speedrun.com/api/v1/';

    public static function search($searchField, $offset) {
        $searchField = urlencode($searchField);
        $url = self::API_URL . "games?name=$searchField&offset=$offset";

        $options = [
            'http' => [
                'method' => 'GET',
            ],
            // TODO Отключаем ssl только для локальной разработки, вынестив в env
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $data = json_decode($response, true);

        return $data;
    }

    public static function getGame($id)
    {
        $url = self::API_URL . "games/$id";

        $options = [
            'http' => [
                'method' => 'GET',
            ],
            // TODO Отключаем ssl только для локальной разработки, вынестив в env
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $data = json_decode($response, true);

        return $data;
    }

    public static function getPlatform($id)
    {
        $url = self::API_URL . "platforms/$id";

        $options = [
            'http' => [
                'method' => 'GET',
            ],
            // TODO Отключаем ssl только для локальной разработки, вынестив в env
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $data = json_decode($response, true);

        return $data;
    }

    public static function getGenres($id)
    {
        $url = self::API_URL . "genres/$id";

        $options = [
            'http' => [
                'method' => 'GET',
            ],
            // TODO Отключаем ssl только для локальной разработки, вынестив в env
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $data = json_decode($response, true);

        return $data;
    }

    public static function getData($id, $type)
    {
        $url = self::API_URL . "$type/$id";

        $options = [
            'http' => [
                'method' => 'GET',
            ],
            // TODO Отключаем ssl только для локальной разработки, вынестив в env
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $data = json_decode($response, true);

        return $data;
    }
}
