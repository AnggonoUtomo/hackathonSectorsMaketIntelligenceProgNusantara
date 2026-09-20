<?php

namespace Tests\Unit\MarketData;

use App\Modules\MarketData\Infrastructure\Sectors\Exception\SectorsApiException;
use App\Modules\MarketData\Infrastructure\Sectors\SectorsApiClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SectorsApiClientTest extends TestCase
{
    public function test_it_sends_authorized_get_requests_to_the_configured_base_url(): void
    {
        config([
            'services.sectors.base_url' => 'https://api.example.test/v2',
            'services.sectors.api_key' => 'test-secret-key',
            'services.sectors.timeout' => 7,
        ]);

        Http::fake([
            'https://api.example.test/v2/companies?keyword=BBCA&page=1' => Http::response([
                'data' => [
                    ['symbol' => 'BBCA'],
                ],
            ]),
        ]);

        $response = app(SectorsApiClient::class)->get('companies', [
            'keyword' => 'BBCA',
            'page' => 1,
        ]);

        $this->assertSame('BBCA', $response['data'][0]['symbol']);

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://api.example.test/v2/companies?keyword=BBCA&page=1'
                && $request->hasHeader('Authorization', 'test-secret-key');
        });
    }

    public function test_it_rejects_requests_when_the_api_key_is_missing(): void
    {
        config([
            'services.sectors.base_url' => 'https://api.example.test/v2',
            'services.sectors.api_key' => null,
        ]);

        $this->expectException(SectorsApiException::class);
        $this->expectExceptionMessage('Sectors API key is not configured.');

        app(SectorsApiClient::class)->get('companies');

        Http::assertNothingSent();
    }

    public function test_it_maps_provider_http_errors_without_exposing_the_api_key(): void
    {
        config([
            'services.sectors.base_url' => 'https://api.example.test/v2',
            'services.sectors.api_key' => 'test-secret-key',
        ]);

        Http::fake([
            'https://api.example.test/v2/companies' => Http::response([
                'message' => 'Unauthorized',
            ], 401),
        ]);

        try {
            app(SectorsApiClient::class)->get('/companies/');
            $this->fail('Expected SectorsApiException.');
        } catch (SectorsApiException $exception) {
            $this->assertSame(401, $exception->providerStatus());
            $this->assertSame('provider_unauthorized', $exception->reason());
            $this->assertStringNotContainsString('test-secret-key', $exception->getMessage());
        }
    }

    /**
     * @return array<int, array{int, string}>
     */
    public static function providerErrorStatusProvider(): array
    {
        return [
            [400, 'provider_bad_request'],
            [403, 'provider_unauthorized'],
            [404, 'provider_not_found'],
            [429, 'provider_rate_limited'],
            [500, 'provider_unavailable'],
        ];
    }

    #[DataProvider('providerErrorStatusProvider')]
    public function test_it_maps_provider_error_statuses(int $status, string $reason): void
    {
        config([
            'services.sectors.base_url' => 'https://api.example.test/v2',
            'services.sectors.api_key' => 'test-secret-key',
        ]);

        Http::fake([
            'https://api.example.test/v2/companies' => Http::response([], $status),
        ]);

        try {
            app(SectorsApiClient::class)->get('companies');
            $this->fail('Expected SectorsApiException.');
        } catch (SectorsApiException $exception) {
            $this->assertSame($status, $exception->providerStatus());
            $this->assertSame($reason, $exception->reason());
        }
    }

    public function test_it_maps_timeout_or_connection_failures(): void
    {
        config([
            'services.sectors.base_url' => 'https://api.example.test/v2',
            'services.sectors.api_key' => 'test-secret-key',
        ]);

        Http::fake(fn () => throw new ConnectionException('Connection timed out'));

        try {
            app(SectorsApiClient::class)->get('companies');
            $this->fail('Expected SectorsApiException.');
        } catch (SectorsApiException $exception) {
            $this->assertNull($exception->providerStatus());
            $this->assertSame('provider_timeout', $exception->reason());
            $this->assertStringNotContainsString('test-secret-key', $exception->getMessage());
        }
    }
}
