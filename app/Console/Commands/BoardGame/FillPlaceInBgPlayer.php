<?php

namespace App\Console\Commands\BoardGame;

use App\Models\BoardGame\BoardGamePlayer;
use Illuminate\Console\Command;

class FillPlaceInBgPlayer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fill-place-in-bg-player {boardGameId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $boardGameId = $this->argument('boardGameId');

        $service = app(BoardGamePlayer::SERVICE);
        $service->recalculatePlaces($boardGameId);
    }
}
