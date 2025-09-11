<?php

namespace App\Console\Commands\BoardGame;

use App\Models\BoardGame\ItemBind;
use App\Models\BoardGame\Item;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FillItemsTableCommand extends Command
{
    protected $signature = 'board-game:fillItemsTable';
    protected $description = 'Заполнение таблицы предметов настольной игры из старой таблицы';

    public function handle()
    {
        $this->line('НАЧАЛО работы комманды заполнения таблицы предметов');

        $oldItems = ItemBind::query()->get();

        foreach ($oldItems as $item) {
            $arItem = [
                'name' => $item->name,
                'slug' => $item->slug,
                'full_description' => $item->description,
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

            DB::table('media_binds')
                ->where('media_bind_type', '=', 'App\Models\BoardGame\ItemBind')
                ->where('media_bind_id', $item->id)
                ->update([
                    'media_bind_id' => $createResult->id,
                    'media_bind_type' => 'App\Models\BoardGame\Item',
                    'updated_at' => now()
                ]);
        }

        $this->line('КОНЕЦ работы комманды заполнения таблицы предметов');
    }
}
