<?php

namespace App\Http\Controllers\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\BoardGame\BoardGameInventoryResource;
use App\Models\BoardGame\BoardGameInventory;
use App\Models\BoardGame\ItemBind;
use App\Models\BoardGame\BoardGamePlayer;
use App\Models\BoardGame\BoardGamePlayerPosition;
use App\Models\BoardGame\PlayerStatusEffect;
use App\Models\BoardGame\StatusEffect;
use App\Models\Media;
use App\Models\User\Notification;
use App\Services\BoardGame\UseItemService;
use App\Services\TagService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BoardGameInventoryController extends Controller
{
    public function index(BoardGameInventory $BoardGameInventory) {
        return $BoardGameInventory::all();
    }

    public function list(Request $request, BoardGameInventory $BoardGameInventory) {
        $user = $request->user();

        if ($user) {
            $inventory = $BoardGameInventory->where('user_id', $user->id)->where('board_game_id', $request->board_game_id)->get();
            return BoardGameInventoryResource::collection($inventory);
        } else {
            return false;
        }
    }

    public function add(Request $request) {
        $fields = $request->validate([
            'board_game_id' => 'required',
            'board_game_item_id' => 'required',

        ]);

        $fields['user_id'] = $request->user()->id;
        $fields['created_by'] = $request->user()->id;

        $user = $request->user();

        if ($user) {
            if ($BoardGameInventory = BoardGameInventory::create($fields)) {
                return $BoardGameInventory;
            }
        }

        return false;
    }

    public function destroy(Request $request, BoardGameInventory $BoardGameInventory) {
        $user = $request->user();

        if ($user) {
            $inventoryItem = $BoardGameInventory->where('id', $request->id)->where('user_id', $user->id)->where('board_game_id', $request->board_game_id);
            return $inventoryItem->delete();
        } else {
            return false;
        }
    }

    public function useItem(Request $request) {
        $useItemService = new UseItemService();
        return $useItemService->useItem($request);
    }

    public function createNotification($player, $user, $request, $notification) {
        if (
            isset($player) && $player
            && isset($request->additionalParams['message'])
            && $request->additionalParams['message']
            && $player->user_id !== $user->id
        ) {
            $fields = [
                'user_id' => $player->user_id,
                'created_by' => $user->id,
                'message' => $request->additionalParams['message'],
            ];

            $notification->create($fields);
        }
    }

    public function setPlayerPoints($request, $player, $action, $BoardGamePlayerPosition) {
        if (is_int($action->value)) {
            if ($action->type === 'addPoints') {
                $playerFields = ['points' => $player->points + $action->value];
            } elseif ($action->type === 'removePoints') {
                $playerFields = ['points' => $player->points - $action->value];
            }
        } else {
            if ($action->value === 'playersAheadCount') {
                $playerPosition = $BoardGamePlayerPosition->where('user_id', $player->user_id)->where('board_game_id', $request->board_game_id)->orderBy('id', 'desc')->first();

                $playersAhead = $BoardGamePlayerPosition::where('position', '>', $playerPosition->position)->where('user_id', '!=', $player->user_id)->get()->sortByDesc('created_at')->unique('user_id');
                $playersAheadCount = count($playersAhead);

                if ($action->type === 'addPoints') {
                    $playerFields = ['points' => $player->points + $playersAheadCount];
                } elseif ($action->type === 'removePoints') {
                    $playerFields = ['points' => $player->points - $playersAheadCount];
                }
            } else if (str_contains($action->value, 'forEachNear')) {
                $explodedString = explode('_', $action->value);

                $currentPlayerPosition = $BoardGamePlayerPosition->where('user_id', $player->user_id)->where('board_game_id', $request->board_game_id)->orderBy('id', 'desc')->first();
                $nearPlayers = $BoardGamePlayerPosition::where('user_id', '!=', $player->user_id)
                    ->get()
                    ->sortByDesc('created_at')
                    ->unique('user_id');

                $count = 0;

                foreach ($nearPlayers as $nearPlayer) {
                    if ($nearPlayer->position >= $currentPlayerPosition->position + (int)$explodedString[1]) {
                        $count++;
                    }

                    if ($nearPlayer->position >= $currentPlayerPosition->position - (int)$explodedString[1]) {
                        $count++;
                    }
                }

                if ($count > 0) {
                    if ($action->type === 'addPoints') {
                        $playerFields = ['points' => $player->points + $count];
                    } elseif ($action->type === 'removePoints') {
                        $playerFields = ['points' => $player->points - $count];
                    }
                } else if ($action->else) {
                    if ($action->else->type === 'addPoints') {
                        $playerFields = ['points' => $player->points + $action->else->value];
                    } elseif ($action->else->type === 'removePoints') {
                        $playerFields = ['points' => $player->points - $action->else->value];
                    }
                }
            }
        }

        return $playerFields;
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required',
            'slug' => 'sometimes',
            'description' => 'sometimes',
            'active' => 'sometimes',
            'image' => 'sometimes',
        ]);

        $fields['created_by'] = $request->user()->id;
        $fields['active'] = true;

        if ($BoardGameInventory = BoardGameInventory::create($fields)) {

            if (isset($fields['image'])) {
                $media = Media::query()->where('id', $fields['image'])->first();
                $BoardGameInventory->media()->syncWithPivotValues($media->id, ['type' => 1]);
            }

            if (isset($fields['tags'])) {
                TagService::attacheTagsToEntity($BoardGameInventory, $fields['tags']);
            }

            return $BoardGameInventory;
        }
    }

    public function update(Request $request, BoardGameInventory $BoardGameInventory) {
        $fields = $request->validate([
            'name' => 'required',
            'slug' => 'sometimes',
            'description' => 'sometimes',
            'active' => 'sometimes',
            'image' => 'sometimes',
        ]);

        if (isset($fields['image'])) {
            $media = Media::query()->where('id', $fields['image'])->first();
            $BoardGameInventory->media()->syncWithPivotValues($media->id, ['type' => 1]);
        }

        if (isset($fields['tags'])) {
            TagService::attacheTagsToEntity($BoardGameInventory, $fields['tags']);
        }

        return $BoardGameInventory->update($fields);
    }

    public function edit(BoardGameInventory $BoardGameInventory)
    {
        return BoardGameInventoryResource::make($BoardGameInventory);
    }
}
