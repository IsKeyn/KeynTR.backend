<?php

namespace App\Events\BoardGame;

use App\Http\Resources\BoardGame\LogResource;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImportantLogs implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $log;

    public function __construct($log)
    {
        $this->log = $log;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel("logs.{$this->log->boardGame->slug}"),
        ];
    }

    public function broadcastWith()
    {
        return LogResource::make($this->log)->toArray(request());
    }
}
