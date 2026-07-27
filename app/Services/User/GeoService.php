<?php

namespace App\Services\User;

use Illuminate\Support\Facades\Http;

class GeoService
{
    /**
     * @param $ip
     * @return array|mixed
     */
    public static function getUserCountry($ip)
    {
        $response = Http::get("http://ip-api.com/json/{$ip}?fields=countryCode");
        return $response->json('countryCode');
    }
}
