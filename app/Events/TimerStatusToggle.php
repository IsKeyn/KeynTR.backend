<?php

namespace App\Events;

use App\Services\BoardGame\TimerService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TimerStatusToggle implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $playerTimer;
    public $bgSlug;
    public $userId;
    public $timerSlug;

    public function __construct($playerTimer)
    {
        $this->playerTimer = $playerTimer;
        $this->bgSlug = $this->playerTimer->boardGame->slug;
        $this->userId = $this->playerTimer->user_id;
        $this->timerSlug = $this->playerTimer->slug;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel("timer.{$this->bgSlug}.{$this->userId}.{$this->timerSlug}"),
        ];
    }

    public function broadcastWith()
    {
        return TimerService::getTimerStatus($this->playerTimer);
    }
}
