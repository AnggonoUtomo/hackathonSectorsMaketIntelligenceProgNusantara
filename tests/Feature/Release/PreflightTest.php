<?php

namespace Tests\Feature\Release;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Mockery;
use Tests\TestCase;

class PreflightTest extends TestCase
{
    private string $publicDirectory;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        $this->publicDirectory = sys_get_temp_dir().'/nusalens-preflight-'.bin2hex(random_bytes(8));
        File::makeDirectory($this->publicDirectory.'/build/assets', 0755, true);
        File::put($this->publicDirectory.'/build/assets/app.js', '// test asset');
        File::put($this->publicDirectory.'/build/manifest.json', json_encode(['resources/js/app.tsx' => ['file' => 'assets/app.js']]));
        $this->app->usePublicPath($this->publicDirectory);
        config(['app.env' => 'production', 'app.debug' => false, 'app.url' => 'https://riset.nusalens.id',
            'app.key' => 'base64:'.base64_encode(str_repeat('x', 32)),
            'session.secure' => true, 'session.http_only' => true, 'session.same_site' => 'lax', 'session.driver' => 'database',
            'database.default' => 'mysql', 'database.connections.mysql.username' => 'nusalens',
            'database.connections.mysql.password' => 'fixture-password', 'database.connections.mysql.database' => 'nusalens',
            'cache.default' => 'redis', 'cache.prefix' => 'nusalens_test', 'queue.default' => 'redis',
            'database.redis.client' => 'phpredis', 'logging.level' => 'info', 'logging.default' => 'daily',
            'logging.channels.daily.level' => 'info', 'marketdata.provider_mode' => 'real',
            'services.sectors.api_key' => 'fixture-key', 'services.sectors.base_url' => 'https://api.sectors.app/v2',
            'mail.default' => 'smtp', 'mail.mailers.smtp.host' => 'smtp.nusalens.id', 'mail.mailers.smtp.port' => 587,
            'mail.mailers.smtp.require_tls' => true, 'mail.mailers.smtp.timeout' => 10,
            'mail.from.address' => 'riset@nusalens.id']);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->publicDirectory);
        parent::tearDown();
    }

    public function test_valid_configuration_passes_without_contacting_provider(): void
    {
        $this->artisan('nusalens:preflight')->assertSuccessful();
        Http::assertNothingSent();
    }

    public function test_unsafe_configuration_fails_without_printing_secrets(): void
    {
        config(['app.debug' => true, 'app.url' => 'http://localhost', 'session.secure' => false,
            'marketdata.provider_mode' => 'fake', 'services.sectors.api_key' => 'SECRET-never-print']);
        $this->assertSame(1, Artisan::call('nusalens:preflight'));
        $output = Artisan::output();
        $this->assertStringContainsString('FAIL', $output);
        $this->assertStringNotContainsString('SECRET-never-print', $output);
        Http::assertNothingSent();
    }

    public function test_missing_build_or_stale_hot_file_blocks_release(): void
    {
        File::delete($this->publicDirectory.'/build/assets/app.js');
        File::put($this->publicDirectory.'/hot', 'http://localhost:5173');
        $this->artisan('nusalens:preflight')->assertFailed();
    }

    public function test_service_failure_does_not_expose_exception_credentials(): void
    {
        DB::shouldReceive('select')->once()->with('SELECT 1')->andThrow(new \RuntimeException('SECRET-db-password'));
        $this->assertSame(1, Artisan::call('nusalens:preflight', ['--check-services' => true]));
        $this->assertStringNotContainsString('SECRET-db-password', Artisan::output());
        Http::assertNothingSent();
    }

    public function test_unsafe_configuration_never_contacts_services(): void
    {
        config(['app.debug' => true]);
        DB::shouldReceive('select')->never();
        Redis::shouldReceive('connection')->never();
        $this->artisan('nusalens:preflight', ['--check-services' => true])->assertFailed();
        Http::assertNothingSent();
    }

    public function test_healthy_services_release_the_probe_lock_without_provider_calls(): void
    {
        $this->mockMigratedDatabase();
        Redis::shouldReceive('connection->ping')->once()->andReturn(true);
        $lock = Mockery::mock();
        $lock->shouldReceive('get')->once()->andReturn(true);
        $lock->shouldReceive('release')->once()->andReturn(true);
        $store = Mockery::mock();
        $store->shouldReceive('lock')->once()->with(Mockery::type('string'), 10)->andReturn($lock);
        Cache::shouldReceive('store')->once()->with('redis')->andReturn($store);
        $this->artisan('nusalens:preflight', ['--check-services' => true])->assertSuccessful();
        Http::assertNothingSent();
    }

    public function test_redis_failure_does_not_expose_exception_credentials(): void
    {
        $this->mockMigratedDatabase();
        Redis::shouldReceive('connection')->once()->andThrow(new \RuntimeException('SECRET-redis-password'));
        $this->assertSame(1, Artisan::call('nusalens:preflight', ['--check-services' => true]));
        $this->assertStringNotContainsString('SECRET-redis-password', Artisan::output());
        Http::assertNothingSent();
    }

    private function mockMigratedDatabase(): void
    {
        DB::shouldReceive('select')->once()->with('SELECT 1')->andReturn([]);
        $migrator = Mockery::mock();
        $migrator->shouldReceive('getMigrationFiles')->once()->with(database_path('migrations'))->andReturn(['initial' => 'initial.php']);
        $migrator->shouldReceive('getRepository->getRan')->once()->andReturn(['initial']);
        $this->app->instance('migrator', $migrator);
    }
}
