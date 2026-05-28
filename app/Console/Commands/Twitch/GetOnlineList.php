<?php

namespace App\Console\Commands\Twitch;

use App\Events\TwitchOnlineStreamers;
use App\Services\TwitchService;
use Illuminate\Console\Command;

class GetOnlineList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twitch:get-online-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Комманда получает список стримеров онлайн';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $twitchService = app(TwitchService::class);
        $result = $twitchService->streamersLive();
        TwitchOnlineStreamers::dispatch($result);
    }
}
