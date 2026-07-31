<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BlockRussianIPs
{
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();

        // Кэшируем результат на 24 часа, чтобы не нагружать БД/API
        $country = Cache::remember("geo_{$ip}", 86400, function () use ($ip) {
            return geoip()->getLocation($ip)->iso_code;
        });

        if ($country === 'RU') {
            // Вариант 1: Отдать ошибку 403
            abort(403, 'Content is not available in your region.');

            // Вариант 2: Редирект на другую страницу
            // return redirect('/restricted');
        }

        // Передаем страну во вьюшки/фронтенд, если нужно
        $request->merge(['user_country' => $country]);

        return $next($request);
    }
}
