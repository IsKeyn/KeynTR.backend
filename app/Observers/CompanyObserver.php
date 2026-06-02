<?php

namespace App\Observers;

use App\Models\Company;
use App\Services\Cache\AdminCacheService;
use App\Services\Observer\DefaultObserverService;

class CompanyObserver
{
    protected DefaultObserverService $defaultObserverService;

    public function __construct(DefaultObserverService $defaultObserverService)
    {
        $this->defaultObserverService = $defaultObserverService;
    }

    /**
     * Handle the Company "created" event.
     *
     * @param  \App\Models\Company  $company
     * @return void
     */
    public function created(Company $company)
    {
        $this->defaultObserverService->created(
            $company,
            'App\Services\Cache\CompanyCacheService',
            'App\Services\CompanyService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the Company "updated" event.
     *
     * @param  \App\Models\Company  $company
     * @return void
     */
    public function updated(Company $company)
    {
        $this->defaultObserverService->updated(
            $company,
            'App\Services\Cache\CompanyCacheService',
            'App\Services\CompanyService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the Company "deleted" event.
     *
     * @param  \App\Models\Company  $company
     * @return void
     */
    public function deleted(Company $company)
    {
        $this->defaultObserverService->deleted(
            $company,
            'App\Services\Cache\CompanyCacheService',
            'App\Services\CompanyService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the Company "restored" event.
     *
     * @param  \App\Models\Company  $company
     * @return void
     */
    public function restored(Company $company)
    {
        $this->defaultObserverService->restored(
            $company,
            'App\Services\Cache\CompanyCacheService',
            'App\Services\CompanyService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }

    /**
     * Handle the Company "force deleted" event.
     *
     * @param  \App\Models\Company  $company
     * @return void
     */
    public function forceDeleted(Company $company)
    {
        $this->defaultObserverService->forceDeleted(
            $company,
            'App\Services\Cache\CompanyCacheService'
        );

        AdminCacheService::clearAdminAdditionalDataCache();
    }
}
