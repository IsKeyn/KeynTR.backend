<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\User\GeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GeoController extends Controller
{
    public function getCountry(Request $request)
    {
        $ip = $request->ip();

//        $ip = '208.67.222.222';
//        $ip = '109.191.177.87';

        $country = Cache::remember("geo_{$ip}", 86400, fn() => GeoService::getUserCountry($ip));
        return response()->json(['country' => $country]);
    }
}
