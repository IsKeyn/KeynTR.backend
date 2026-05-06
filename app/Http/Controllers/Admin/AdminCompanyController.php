<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use App\Services\Entity\DefaultAdminEntityService;
use Illuminate\Http\Request;

class AdminCompanyController extends Controller {
    protected DefaultAdminEntityService $defaultAdminEntityService;

    public function __construct(DefaultAdminEntityService $defaultAdminEntityService)
    {
        $this->defaultAdminEntityService = $defaultAdminEntityService;
    }

    public function index(Request $request)
    {
        return $this->defaultAdminEntityService->index(
            $request,
            'App\Models\Company',
            'App\Services\Cache\CompanyCacheService',
            'App\Filters\CompanyFilter',
            'App\Http\Resources\Admin\Company\ListResource',
        );
    }

    public function store(CompanyRequest $request)
    {
        return $this->defaultAdminEntityService->store(
            $request,
            'App\Models\Company'
        );
    }

    public function update(CompanyRequest $request, Company $company)
    {
        return $this->defaultAdminEntityService->update(
            $request,
            $company
        );
    }

    public function edit(Request $request, $id)
    {
        return $this->defaultAdminEntityService->edit(
            $request,
            $id,
            'App\Services\CompanyService'
        );
    }

    public function destroy(Company $company)
    {
        return $this->defaultAdminEntityService->destroy($company);
    }

    public function forceDelete($id)
    {
        return $this->defaultAdminEntityService->forceDelete(
            'App\Models\Company',
            $id
        );
    }

    public function recovery($id)
    {
        return $this->defaultAdminEntityService->recovery(
            'App\Models\Company',
            $id
        );
    }
}
