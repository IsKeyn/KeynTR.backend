<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MovieRequest;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\GenreResource;
use App\Http\Resources\GroupResource;
use App\Models\Company;
use App\Models\Genre;
use App\Models\Group;
use App\Models\Movie;
use App\Services\Cache\MovieCacheService;
use App\Services\Entity\DefaultAdminEntityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminMovieController extends Controller {
    protected DefaultAdminEntityService $defaultAdminEntityService;

    public function __construct(DefaultAdminEntityService $defaultAdminEntityService)
    {
        $this->defaultAdminEntityService = $defaultAdminEntityService;
    }

    public function index(Request $request)
    {
        return $this->defaultAdminEntityService->index(
            $request,
            'App\Models\Movie',
            'App\Services\Cache\MovieCacheService',
            'App\Filters\MovieFilter',
            'App\Http\Resources\Admin\Movie\ListResource',
        );
    }

    public function store(MovieRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            'App\Models\Movie'
        );
    }

    public function update(MovieRequest $request, Movie $Movie)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $Movie
        );
    }

    public function edit(Request $request, $id)
    {
        return $this->defaultAdminEntityService->edit(
            $request,
            $id,
            'App\Services\MovieService'
        );
    }

    public function destroy(Movie $Movie)
    {
        return $this->defaultAdminEntityService->destroy($Movie);
    }

    public function forceDelete($id)
    {
        return $this->defaultAdminEntityService->forceDelete(
            'App\Models\Movie',
            $id
        );
    }

    public function recovery($id)
    {
        return $this->defaultAdminEntityService->recovery(
            'App\Models\Movie',
            $id
        );
    }

    public function getListFilters(Request $request)
    {
        return $this->defaultAdminEntityService->getListFilters(
            $request,
            'App\Models\Movie',
            'App\Filters\MovieFilter',
            'App\Services\Cache\MovieCacheService',
        );
    }

    public function getAdditionalData()
    {
        return Cache::remember(MovieCacheService::ADMIN_ADDDATA_PREFIX, MovieCacheService::TIME, function () {
            return [
                'genre' => GenreResource::collection(Genre::all()),
                'company' => CompanyResource::collection(Company::all()),
                'company_role' => GroupResource::collection(Group::where('entity_type', 'App\Models\Company')->get()),
            ];
        });
    }
}
