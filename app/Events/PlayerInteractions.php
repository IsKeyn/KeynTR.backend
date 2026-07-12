<?php

namespace App\Events;

use App\Http\Resources\BoardGame\Board\BgPlayerInteractionResource;
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

    public function __construct($userId, $playerInteractions)
    {
        $this->userId = $userId;
        $this->playerInteractions = $playerInteractions;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("App.Models.User.{$this->userId}"),
        ];
    }

    public function broadcastWith()
    {
        return BgPlayerInteractionResource::collection($this->playerInteractions)->toArray(request());
    }
}
