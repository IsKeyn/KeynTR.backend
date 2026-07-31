<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MovePlayer implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $moveData;

    public function __construct($moveData)
    {
        $this->moveData = $moveData;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel("MovePlayer"),
        ];
    }

    public function broadcastWith()
    {
        return $this->moveData;
    }
}
