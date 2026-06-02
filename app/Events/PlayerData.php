<?php

namespace App\Events;

use App\Services\BoardGame\BgPlayerService;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerData implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $player;
    public $bgSlug;
    public $userId;

    public function __construct($player)
    {
        $this->player = $player;
        $this->bgSlug = $this->player->boardGame->slug;
        $this->userId = $this->player->user->id;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("bgPlayer.{$this->bgSlug}.{$this->userId}"),
        ];
    }

    public function broadcastWith()
    {
        return BgPlayerService::getCurrent($this->bgSlug)->toArray(request());
    }
}
