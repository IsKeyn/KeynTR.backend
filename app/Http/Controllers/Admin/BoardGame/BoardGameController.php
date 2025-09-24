<?php
namespace App\Http\Controllers\Admin\BoardGame;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\BoardGame\BoardGameResource;
use App\Jobs\BoardGame\BoardGameShortCacheClear;
use App\Models\BoardGame\BoardGame;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class BoardGameController extends Controller {

    // TODO почему то не работает связка BoardGame $boardGame, почему? Что-то с моделью

    public function index()
    {
        return BoardGameResource::collection(BoardGame::query()->get());
    }

    public function store(Request $request)
    {
        $params = $request->all();
        $entity = BoardGame::create($params);

        $mediaService = new MediaService();

        if (isset($params['media'])) {
            $mediaService->setTitleImage($entity, $params['media']);
        }

        $this->clearCache($entity);

        return $entity;
    }

    public function update(Request $request, $id)
    {
        $boardGame = BoardGame::findById($id)->first();

        $params = $request->all();

        $mediaService = new MediaService();


        if (isset($params['media'])) {
            $mediaService->setTitleImage($boardGame, $params['media']);
        }

        $this->clearCache($boardGame);

        return $boardGame->update($params);
    }

    private function clearCache($entity)
    {
        Cache::forget('board_game_' . $entity->slug . '_short_cache');
        BoardGameShortCacheClear::dispatch($entity->slug)->delay(Carbon::create($entity->ended_at));
    }

    public function edit($id) {
        $boardGame = BoardGame::findById($id)->first();

        return BoardGameResource::make($boardGame);
    }

    public function destroy($id) {
        // TODO Очищать все привязки, операция опасна, может добавить soft delete а удалять полностью по команде?
        $boardGame = BoardGame::findById($id)->first();
        return $boardGame->delete();
    }
}
