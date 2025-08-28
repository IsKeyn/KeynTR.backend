<?php

namespace App\Console\Commands\BoardGame;

use App\Models\BoardGame\BoardGameItem;
use App\Models\BoardGame\Item;
use Illuminate\Console\Command;

class FillItemsTableCommand extends Command
{
    protected $signature = 'board-game:fillItemsTable';
    protected $description = 'Заполнение таблицы предметов настольной игры из старой таблицы';

    public function handle()
    {
        $this->line('НАЧАЛО работы комманды заполнения таблицы предметов');

        $oldItems = BoardGameItem::query()->get();

        foreach ($oldItems as $item) {
            $arItem = [
                'name' => $item->name,
                'slug' => $item->slug,
                'description' => $item->description,
                'actions' => $item->actions,
                'type' => $item->type,
                'active' => $item->active,
                'author' => $item->author,
                'created_by' => $item->created_by,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];

            $createResult = Item::create($arItem);
            $item->update([
                'item_id' => $createResult->id,
            ]);
        }

        $this->line('КОНЕЦ работы комманды заполнения таблицы предметов');
    }
}
