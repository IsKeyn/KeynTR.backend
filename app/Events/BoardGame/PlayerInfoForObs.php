<?php

namespace App\Events\BoardGame;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerInfoForObs implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $player;

    public function __construct($player)
    {
        $this->player = $player;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel("playerInfoForObs.{$this->player->id}"),
        ];
    }

    public function broadcastWith()
    {
        return [
            'status' => 'update',
        ];
    }
}
