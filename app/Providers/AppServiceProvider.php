<?php

namespace App\Providers;

use App\Modules\MarketData\Application\Contracts\CompanyDirectory;
use App\Modules\MarketData\Infrastructure\Sectors\SectorsCompanyDirectory;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CompanyDirectory::class, SectorsCompanyDirectory::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
