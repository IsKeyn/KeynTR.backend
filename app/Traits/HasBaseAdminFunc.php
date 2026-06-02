<?php
namespace App\Traits;

use Illuminate\Http\Request;

trait HasBaseAdminFunc
{
    public function index(Request $request)
    {
        return $this->defaultAdminEntityService->index(
            $request,
            self::MODEL,
            self::CACHE_SERVICE,
            self::FILTER,
            self::LIST_RESOURCE,
        );
    }

    public function edit(Request $request, $id)
    {
        return $this->defaultAdminEntityService->edit(
            $request,
            $id,
            self::SERVICE
        );
    }

    public function forceDelete($id)
    {
        return $this->defaultAdminEntityService->forceDelete(
            self::MODEL,
            $id
        );
    }

    public function recovery($id)
    {
        return $this->defaultAdminEntityService->recovery(
            self::MODEL,
            $id
        );
    }

    public function getListFilters(Request $request)
    {
        return $this->defaultAdminEntityService->getListFilters(
            $request,
            self::MODEL,
            self::FILTER,
            self::CACHE_SERVICE,
        );
    }
}
