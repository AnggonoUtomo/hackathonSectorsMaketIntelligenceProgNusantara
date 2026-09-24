<?php

namespace App\Providers;

use App\Modules\MarketData\Application\Contracts\CompanyAnalytics;
use App\Modules\MarketData\Application\Contracts\CompanyDirectory;
use App\Modules\MarketData\Infrastructure\Sectors\SectorsCompanyAnalytics;
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
        $this->app->bind(CompanyAnalytics::class, SectorsCompanyAnalytics::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
