<?php

namespace App\Providers;

use App\Modules\Company\Application\Contracts\CompanyResearchData;
use App\Modules\Company\Application\Contracts\FactAttestation;
use App\Modules\Company\Application\GetCompanyResearchData;
use App\Modules\Company\Infrastructure\EncryptedFactAttestation;
use App\Modules\Comparison\Application\Contracts\ComparisonSnapshotStore;
use App\Modules\Comparison\Infrastructure\Persistence\EloquentComparisonSnapshotStore;
use App\Modules\Intelligence\Application\Contracts\PeerResearch;
use App\Modules\Intelligence\Application\Contracts\ScoreEvidence;
use App\Modules\Intelligence\Application\FindPeerResearch;
use App\Modules\Intelligence\Infrastructure\Persistence\DatabaseScoreEvidence;
use App\Modules\MarketData\Application\Contracts\CompanyAnalytics;
use App\Modules\MarketData\Application\Contracts\CompanyDirectory;
use App\Modules\MarketData\Application\Contracts\PeerFinancialData;
use App\Modules\MarketData\Application\Contracts\PeerMarketData;
use App\Modules\MarketData\Infrastructure\Sectors\SectorsCompanyAnalytics;
use App\Modules\MarketData\Infrastructure\Sectors\SectorsCompanyDirectory;
use App\Modules\MarketData\Infrastructure\Sectors\SectorsPeerFinancialData;
use App\Modules\MarketData\Infrastructure\Sectors\SectorsPeerMarketData;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PeerResearch::class, FindPeerResearch::class);
        $this->app->bind(PeerMarketData::class, SectorsPeerMarketData::class);
        $this->app->bind(FactAttestation::class, EncryptedFactAttestation::class);
        $this->app->bind(PeerFinancialData::class, SectorsPeerFinancialData::class);
        $this->app->bind(ScoreEvidence::class, DatabaseScoreEvidence::class);
        $this->app->bind(CompanyResearchData::class, GetCompanyResearchData::class);
        $this->app->bind(ComparisonSnapshotStore::class, EloquentComparisonSnapshotStore::class);
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
