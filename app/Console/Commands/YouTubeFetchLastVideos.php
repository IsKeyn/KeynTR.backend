<?php

namespace App\Console\Commands;

use App\Http\Controllers\YouTubeController;
use Illuminate\Console\Command;

class YouTubeFetchLastVideos extends Command
{
    protected $signature = 'YouTube:FetchLastVideos';
    protected $description = 'Command description';

    public function handle()
    {
        $YouTubeController = new YouTubeController();
        $YouTubeController->getLastVideosFromApi();
        $this->line('ЗАКОНЧЕНО получение новых видео с YouTube');
    }
}
