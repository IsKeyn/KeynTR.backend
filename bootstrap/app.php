<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\CheckIsAdmin;
use App\Http\Middleware\RedirectIfAuthenticated;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

    $middleware->statefulApi();
    // ─────────────────────────────────────────
    // Глобальные мидлвары (было в $middleware)
    // ─────────────────────────────────────────
    $middleware->append([
        \Illuminate\Http\Middleware\HandleCors::class,
        TrustProxies::class,
        PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ]);

    // ─────────────────────────────────────────
    // Группа 'web'
    // ─────────────────────────────────────────
    $middleware->group('web', [
        EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        VerifyCsrfToken::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ]);

    // ─────────────────────────────────────────
    // Группа 'api'
    // ─────────────────────────────────────────
    $middleware->group('api', [
        EnsureFrontendRequestsAreStateful::class,
        'throttle:api',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ]);

    // ─────────────────────────────────────────
    // Алиасы мидлваров (было в $routeMiddleware)
    // ─────────────────────────────────────────
    $middleware->alias([
        'auth' => Authenticate::class,
        'is_admin' => CheckIsAdmin::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'bg.check.full_condition' => \App\Http\Middleware\BoardGame\CheckConditions::class,
        'bg.check.debug' => \App\Http\Middleware\BoardGame\CheckDebugMode::class,
        'bg.check.is' => \App\Http\Middleware\BoardGame\CheckIsBg::class,
        'bg.check.is_coming_soon' => \App\Http\Middleware\BoardGame\CheckIsBgComingSoonStatus::class,
        'bg.check.is_open' => \App\Http\Middleware\BoardGame\CheckIsBgOpenStatus::class,
        'bg.check.is_close' => \App\Http\Middleware\BoardGame\CheckIsBgClose::class,
        'bg.check.dont_close' => \App\Http\Middleware\BoardGame\CheckIsBgDontClose::class,
        'bg.check.active_player' => \App\Http\Middleware\BoardGame\CheckIsPlayer::class,
    ]);
})
    ->withSchedule(function (Schedule $schedule) {
        // ─────────────────────────────────────────
        // Расписание задач (было в App\Console\Kernel)
        // ─────────────────────────────────────────
        $schedule->command('YouTube:FetchLastVideos')->daily();
        $schedule->command('auth:clear-resets')->everyFifteenMinutes();
        $schedule->command('views:count')->everyFifteenMinutes();
        $schedule->command('user:clear-magic-links')->daily();
        $schedule->command('sanctum:prune-expired')->daily();
        $schedule->command('board-game:unset-players-streak')->sundays()->at('23:59');
        $schedule->command('board-game:stop-limited-timer')->everyFifteenMinutes();
        $schedule->command('version:clear-versions 365')->sundays()->at('23:59');
        $schedule->command('twitch:get-online-list')->everyFiveMinutes();
        $schedule->command('board-game:set-status-effect-on-player-command')->dailyAt('12:00');

        // Закомментированные задачи при необходимости:
        // $schedule->command('log:set')->everyMinute();
        // $schedule->command('inspire')->hourly();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // ─────────────────────────────────────────
        // Обработка исключений
        // ─────────────────────────────────────────
        // Пример кастомной обработки:
        // $exceptions->render(function (Throwable $e, Illuminate\Http\Request $request) {
        //     if ($e instanceof \App\Exceptions\CustomException) {
        //         return response()->json(['error' => $e->getMessage()], 500);
        //     }
        // });
    })->create();
