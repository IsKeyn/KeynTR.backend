<?php

namespace App\Events;

use App\Http\Resources\Event\StreamersOnlineResource;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TwitchOnlineStreamers implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $streamersOnline;

    public function __construct($streamersOnline)
    {
        $this->streamersOnline = $streamersOnline;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel("TwitchOnlineStreamers"),
        ];
    }

    public function broadcastWith()
    {
        return StreamersOnlineResource::collection($this->streamersOnline)->toArray(request());
    }
}
