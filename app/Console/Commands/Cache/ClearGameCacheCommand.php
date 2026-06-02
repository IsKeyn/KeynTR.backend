<?php

namespace App\Console\Commands\Cache;

use App\Services\Cache\MediaCacheService;
use Illuminate\Console\Command;
use App\Services\Cache\GameCacheService;

class ClearGameCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-games';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Очистка кеша игр';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $cacheService = app(MediaCacheService::class);

        $cacheService->clearAllCache();

//        $cacheService = app(GameCacheService::class);
//
//        $cacheService->clearAllGameCache();

        return 0;
    }
}
