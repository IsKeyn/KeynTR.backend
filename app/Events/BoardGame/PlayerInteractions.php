<?php

namespace App\Events\BoardGame;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerInteractions implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $playerInteractions;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("App.Models.User.{$this->userId}"),
        ];
    }

    public function broadcastWith()
    {
        return [
            'status' => 'update',
        ];
    }
}
