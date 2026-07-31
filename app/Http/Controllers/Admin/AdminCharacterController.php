<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CharacterRequest;
use App\Http\Resources\Game\GameShortestResource;
use App\Models\Character;
use App\Models\Game;
use App\Services\Cache\CharacterCacheService;
use App\Services\Entity\DefaultAdminEntityService;
use App\Traits\HasBaseAdminFunc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminCharacterController extends Controller {
    use HasBaseAdminFunc;

    private const MODEL = Character::class;
    private const CACHE_SERVICE = Character::CACHE_SERVICE;
    private const FILTER = Character::FILTER;
    private const DETAIL_RESOURCE = Character::DETAIL_RESOURCE;
    private const LIST_RESOURCE = Character::LIST_RESOURCE;
    private const SERVICE = Character::SERVICE;

    protected DefaultAdminEntityService $defaultAdminEntityService;

    public function __construct(DefaultAdminEntityService $defaultAdminEntityService)
    {
        $this->defaultAdminEntityService = $defaultAdminEntityService;
    }

    public function index(Request $request)
    {
        return $this->defaultAdminEntityService->index(
            $request,
            self::MODEL,
            self::CACHE_SERVICE,
            self::FILTER,
            self::LIST_RESOURCE,
            false,
        );
    }

    public function store(CharacterRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            self::MODEL
        );
    }

    public function update(CharacterRequest $request, Character $character)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $character
        );
    }

    public function destroy(Character $character)
    {
        return $this->defaultAdminEntityService->destroy($character);
    }

    public function getAdditionalData()
    {
        return Cache::remember(
            CharacterCacheService::ADMIN_ADDDATA_PREFIX, CharacterCacheService::TIME,
            function () {
            return [
                'game' => GameShortestResource::collection(
                    Game::query()->with(['titleImage'])->orderByRaw('sort IS NULL, sort ASC')->get()
                ),
            ];
        });
    }
}
