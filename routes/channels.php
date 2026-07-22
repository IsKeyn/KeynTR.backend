<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::routes([
    'middleware' => ['auth:sanctum']
]);

/* Приватные каналы */
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('bgPlayer.{bgSlug}.{userId}', function ($user, $bgSlug, $userId) {
    return (int) $user->id === (int) $userId;
});

/* Публичные каналы */
Broadcast::channel('timer.${bgSlug}.${userId}.${slug}', function () { return true; });
Broadcast::channel('playerInfoForObs.${bgSlug}.${playerId}', function () { return true; });
Broadcast::channel('logs.${bgSlug}', function () { return true; });
Broadcast::channel('TwitchOnlineStreamers', function () { return true; });
Broadcast::channel('MovePlayer', function () { return true; });
