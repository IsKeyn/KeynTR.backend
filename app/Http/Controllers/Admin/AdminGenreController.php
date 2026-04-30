<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRequest;
use App\Http\Requests\GenreRequest;
use App\Models\Genre;
use App\Services\Entity\DefaultAdminEntityService;
use Illuminate\Http\Request;

class AdminGenreController extends Controller {
    protected DefaultAdminEntityService $defaultAdminEntityService;

    public function __construct(DefaultAdminEntityService $defaultAdminEntityService)
    {
        $this->defaultAdminEntityService = $defaultAdminEntityService;
    }

    public function index(Request $request)
    {
        return $this->defaultAdminEntityService->index(
            $request,
            'App\Models\Genre',
            'App\Services\Cache\GenreCacheService',
            'App\Filters\GenreFilter',
            'App\Http\Resources\Admin\Genre\ListResource',
        );
    }

    public function store(CompanyRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            'App\Models\Genre'
        );
    }

    public function update(GenreRequest $request, Genre $genre)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            $genre
        );
    }

    public function edit(Request $request, $id)
    {
        return $this->defaultAdminEntityService->edit(
            $request,
            $id,
            'App\Services\GenreService'
        );
    }

    public function destroy(Genre $genre)
    {
        return $this->defaultAdminEntityService->destroy($genre);
    }

    public function forceDelete($id)
    {
        return $this->defaultAdminEntityService->forceDelete(
            'App\Models\Genre',
            $id
        );
    }

    public function recovery($id)
    {
        return $this->defaultAdminEntityService->recovery(
            'App\Models\Genre',
            $id
        );
    }
}
