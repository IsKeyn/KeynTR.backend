<?php

namespace App\Console\Commands\BoardGame;

use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\Timer;
use App\Services\BoardGame\TimerService;
use Illuminate\Console\Command;

class FillPointsPerHourInBgPlayersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fill-points-per-hour-in-bg-players-command';

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
        $this->line('НАЧАЛО работы комманды');

        $players = BoardGamePlayer::query()
            ->with([
                'positions' => function ($query) {
                    $query->orderBy('id', 'desc');
                },
            ])
            ->get();

        foreach ($players as $player) {
            $fullPoints = $player->points;

            $positions = $player->positions->first();

            if ($positions && $positions->position) {
                $fullPoints += $player->positions->first()->position;
            }

            $timer = Timer::with('playerTimer')
                ->select(['id', 'name', 'limit', 'user_id', 'board_game_id', 'slug', 'settings', 'elapsed_seconds'])
                ->where('slug', 'main')
                ->where('user_id', $player->user_id)
                ->where('board_game_id', $player->board_game_id)
                ->first();

            $player->points_per_hour = $timer->elapsed_seconds ? round(($fullPoints / $timer->elapsed_seconds) * 3600) : 0;
            $player->saveQuietly();
        }

        $this->line('КОНЕЦ работы комманды');
    }
}
