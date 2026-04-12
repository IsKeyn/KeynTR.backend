<?php

namespace App\Services\Speedruncom;

class SpeedrunApiService
{
    const API_URL = 'https://www.speedrun.com/api/v1/';

    public static function search($searchField, $offset)
    {
        $searchField = urlencode($searchField);
        $url = self::API_URL . "games?name=$searchField&offset=$offset";

        $options = [
            'http' => [
                'method' => 'GET',
            ],
            'ssl' => [
                'verify_peer' => config('ssl.verify_peer'),
                'verify_peer_name' => config('ssl.verify_peer_name'),
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        return json_decode($response, true);
    }

    public static function getGame($id)
    {
        return self::getData($id, 'games');
    }

    public static function getPlatform($id)
    {
        return self::getData($id, 'platforms');
    }

    public static function getGenres($id)
    {
        return self::getData($id, 'genres');
    }

    public static function getData($id, $type)
    {
        $url = self::API_URL . "$type/$id";

        $options = [
            'http' => [
                'method' => 'GET',
            ],
            'ssl' => [
                'verify_peer' => config('ssl.verify_peer'),
                'verify_peer_name' => config('ssl.verify_peer_name'),
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        return json_decode($response, true);
    }
}
