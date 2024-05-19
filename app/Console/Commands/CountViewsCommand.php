<?php

namespace App\Console\Commands;

use App\Services\ViewsService;
use Illuminate\Console\Command;

class CountViewsCommand extends Command
{
    protected $signature = 'views:count';


    public function handle()
    {
        $this->line('НАЧАЛО Подсчет просмотров');

        ViewsService::calcAllVotes();

        $this->line('ЗАКОНЧЕНО Подсчет просмотров');
        return 0;
    }
}
