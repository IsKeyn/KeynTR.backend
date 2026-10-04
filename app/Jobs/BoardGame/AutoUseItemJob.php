<?php

namespace App\Jobs\BoardGame;

use App\Services\BoardGame\UseItemService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\BoardGame\BoardGameInventory;

class AutoUseItemJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param array<string, mixed> $conditionData
     */
    public function __construct(
        private readonly array $conditionData,
        private readonly BoardGameInventory $inventoryItem,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $useItemService = new UseItemService($this->conditionData);
        $useItemService->useItem((object)['id' => $this->inventoryItem->id]);
    }
}
