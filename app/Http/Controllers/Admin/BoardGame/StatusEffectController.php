<?php

namespace App\Http\Controllers\Admin\BoardGame;

use App\Http\Requests\BoardGame\BgStatusEffectRequest;
use App\Models\BoardGame\BoardGame;
use App\Models\BoardGame\Item;
use App\Models\BoardGame\StatusEffect;
use App\Http\Controllers\Controller;
use App\Services\Entity\DefaultAdminEntityService;
use App\Traits\HasBaseAdminFunc;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\Admin\BoardGame\ItemResource;
use Illuminate\Support\Facades\Cache;

class StatusEffectController extends Controller
{
    use HasBaseAdminFunc;

    private const MODEL = StatusEffect::class;
    private const CACHE_SERVICE = StatusEffect::CACHE_SERVICE;
    private const FILTER = StatusEffect::FILTER;
    private const DETAIL_RESOURCE = StatusEffect::DETAIL_RESOURCE;
    private const LIST_RESOURCE = StatusEffect::LIST_RESOURCE;
    private const SERVICE = StatusEffect::SERVICE;

    protected DefaultAdminEntityService $defaultAdminEntityService;

    public function __construct(DefaultAdminEntityService $defaultAdminEntityService)
    {
        $this->defaultAdminEntityService = $defaultAdminEntityService;
    }

    public function store(BgStatusEffectRequest $request): JsonResponse
    {
        return $this->defaultAdminEntityService->store(
            $request,
            self::MODEL
        );
    }

    public function update(BgStatusEffectRequest $request, StatusEffect $statusEffect)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $statusEffect
        );
    }

    public function destroy(StatusEffect $statusEffect)
    {
        return $this->defaultAdminEntityService->destroy($statusEffect);
    }

    public function list(Item $Item)
    {
        return ItemResource::collection($Item::all());
    }

    public function validateFields($request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'slug' => 'sometimes',
            'short_description' => 'sometimes',
            'full_description' => 'sometimes',
            'actions' => 'sometimes',
            'type' => 'sometimes',
            'active' => 'sometimes',
            'drop_chance' => 'sometimes',
            'author' => 'sometimes',
            'created_by' => 'sometimes',
            'image' => 'sometimes',
            'sound' => 'sometimes',
        ]);

        $validated['type'] = $validated['type'] ? $validated['type'] : 0;

        return $validated;
    }

//    public function store(Request $request)
//    {
//        $fields = $this->validateFields($request);
//
//        $fields['created_by'] = $request->user()->id;
//
//        if ($Item = Item::create($fields)) {
//            // Обрабатываем изображение
//            if (isset($fields['image'])) {
//                $media = Media::query()->where('id', $fields['image'])->first();
//                if ($media) {
//                    // Удаляем все медиа с типом изображения
//                    $Item->media()->wherePivot('type', Media::TITLE_TYPE)->detach();
//                    // Добавляем новое изображение
//                    $Item->media()->attach($media->id, ['type' => Media::TITLE_TYPE]);
//                }
//            }
//
//            // Обрабатываем звук
//            if (isset($fields['sound'])) {
//                $media = Media::query()->where('id', $fields['sound'])->first();
//                if ($media) {
//                    // Удаляем все медиа с типом звука
//                    $Item->media()->wherePivot('type', Media::SOUND)->detach();
//                    // Добавляем новый звук
//                    $Item->media()->attach($media->id, ['type' => Media::SOUND]);
//                }
//            }
//
//            if (isset($fields['tags'])) {
//                TagService::attacheTagsToEntity($Item, $fields['tags']);
//            }
//
//            $this->clearCache();
//            return $Item;
//        }
//    }
//
//    public function update(Request $request, Item $Item)
//    {
//        $fields = $this->validateFields($request);
//
//        // Обрабатываем изображение
//        if (isset($fields['image'])) {
//            $media = Media::query()->where('id', $fields['image'])->first();
//            if ($media) {
//                // Удаляем все медиа с типом изображения
//                $Item->media()->wherePivot('type', Media::TITLE_TYPE)->detach();
//                // Добавляем новое изображение
//                $Item->media()->attach($media->id, ['type' => Media::TITLE_TYPE]);
//            }
//        }
//
//        // Обрабатываем звук
//        if (isset($fields['sound'])) {
//            $media = Media::query()->where('id', $fields['sound'])->first();
//            if ($media) {
//                // Удаляем все медиа с типом звука
//                $Item->media()->wherePivot('type', Media::SOUND)->detach();
//                // Добавляем новый звук
//                $Item->media()->attach($media->id, ['type' => Media::SOUND]);
//            }
//        }
//
//        if (isset($fields['tags'])) {
//            TagService::attacheTagsToEntity($Item, $fields['tags']);
//        }
//
//        $this->clearCache();
//        return $Item->update($fields);
//    }

    private function clearCache()
    {
        $boardGames = BoardGame::all();

        foreach ($boardGames as $boardGame) {
            Cache::forget('board_game_' . $boardGame->slug . '_item_list_cache');
        }
    }
}
