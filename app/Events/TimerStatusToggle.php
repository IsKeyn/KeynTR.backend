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

    public $timer;
    public $bgSlug;
    public $userId;
    public $timerSlug;

    public function __construct($timer, $type = 'timer')
    {
        if ($type === 'timer') {
            $this->timer = $timer;
            $this->bgSlug = $this->timer->boardGame->slug;
            $this->userId = $this->timer->user_id;
            $this->timerSlug = $this->timer->slug;
        } else if ($type === 'timer-logs') {
            $this->timer = $timer->timer;
            $this->bgSlug = $this->timer->boardGame->slug;
            $this->userId = $this->timer->user_id;
            $this->timerSlug = $this->timer->slug;
        }
    }

    public function broadcastOn(): array
    {
        return [
            new Channel("timer.{$this->bgSlug}.{$this->userId}.{$this->timerSlug}"),
        ];
    }

    public function broadcastWith()
    {
        return TimerService::getTimerStatus($this->timer);
    }
}
