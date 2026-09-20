<?php

namespace Tests\Feature\MarketData;

use App\Models\User;
use App\Modules\MarketData\Application\DTO\StructuredScreenerCriteria;
use App\Modules\MarketData\Application\Exception\InvalidScreenerCriteriaException;
use App\Modules\MarketData\Infrastructure\Sectors\Exception\SectorsApiException;
use App\Modules\MarketData\Infrastructure\Sectors\StructuredCompanyScreener;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StructuredCompanyScreenerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_fetches_structured_companies_screener_with_pagination(): void
    {
        Cache::flush();
        config([
            'services.sectors.api_key' => 'test-sectors-key',
            'services.sectors.base_url' => 'https://api.example.test/v2',
        ]);

        Http::fake([
            'https://api.example.test/v2/companies*' => Http::response([
                'data' => [
                    [
                        'symbol' => 'BBCA',
                        'company_name' => 'Bank Central Asia Tbk',
                        'sector' => 'Financials',
                        'sub_sector' => 'Banks',
                        'query_values' => [
                            'return_on_equity' => 21.1234,
                        ],
                    ],
                ],
                'meta' => [
                    'current_page' => 2,
                    'per_page' => 10,
                    'total' => 31,
                    'last_page' => 4,
                ],
            ]),
        ]);

        $user = User::factory()->create();
        $criteria = StructuredScreenerCriteria::fromFilters(
            filters: [
                ['field' => 'sector', 'operator' => 'eq', 'value' => 'Financials'],
                ['field' => 'market_cap', 'operator' => 'gte', 'value' => 1000000000000],
            ],
            page: 2,
            perPage: 10,
        );

        $result = app(StructuredCompanyScreener::class)->screen(
            userId: $user->id,
            criteria: $criteria,
            correlationId: 'corr-screener-success',
        );

        $this->assertSame(2, $result->page);
        $this->assertSame(10, $result->perPage);
        $this->assertSame(31, $result->total);
        $this->assertSame(4, $result->lastPage);
        $this->assertCount(1, $result->companies);
        $this->assertSame('BBCA', $result->companies[0]->symbol);
        $this->assertSame('Bank Central Asia Tbk', $result->companies[0]->name);
        $this->assertSame('Banks', $result->companies[0]->subSector);
        $this->assertSame(['return_on_equity' => 21.1234], $result->companies[0]->queryValues);
        $this->assertDatabaseHas('market_data_credit_reservations', [
            'user_id' => $user->id,
            'endpoint' => 'companies',
            'estimated_credits' => 1,
            'correlation_id' => 'corr-screener-success',
        ]);

        Http::assertSent(function ($request): bool {
            $query = $request->data();
            $structuredQuery = json_decode((string) $query['query'], true);

            return $request->url() === 'https://api.example.test/v2/companies?page=2&per_page=10&query='.urlencode((string) $query['query'])
                && $request->hasHeader('Authorization', 'test-sectors-key')
                && $query['page'] === 2
                && $query['per_page'] === 10
                && $structuredQuery === [
                    ['field' => 'sector', 'operator' => 'eq', 'value' => 'Financials'],
                    ['field' => 'market_cap', 'operator' => 'gte', 'value' => 1000000000000],
                ];
        });
    }

    public function test_cache_hit_does_not_call_provider_or_reserve_credit_again(): void
    {
        Cache::flush();
        config([
            'services.sectors.api_key' => 'test-sectors-key',
            'services.sectors.base_url' => 'https://api.example.test/v2',
        ]);

        Http::fake([
            'https://api.example.test/v2/companies*' => Http::response([
                'data' => [
                    [
                        'symbol' => 'BBRI',
                        'name' => 'Bank Rakyat Indonesia Tbk',
                    ],
                ],
                'meta' => [
                    'current_page' => 1,
                    'per_page' => 5,
                    'total' => 1,
                    'last_page' => 1,
                ],
            ]),
        ]);

        $user = User::factory()->create();
        $criteria = StructuredScreenerCriteria::fromFilters(
            filters: [['field' => 'sector', 'operator' => 'eq', 'value' => 'Financials']],
            page: 1,
            perPage: 5,
        );
        $screener = app(StructuredCompanyScreener::class);

        $first = $screener->screen($user->id, $criteria, 'corr-screener-cache');
        $second = $screener->screen($user->id, $criteria, 'corr-screener-cache-second');

        $this->assertSame('BBRI', $first->companies[0]->symbol);
        $this->assertSame('BBRI', $second->companies[0]->symbol);
        $this->assertDatabaseCount('market_data_credit_reservations', 1);
        Http::assertSentCount(1);
    }

    public function test_it_rejects_raw_or_unknown_screener_expression(): void
    {
        $this->expectException(InvalidScreenerCriteriaException::class);
        $this->expectExceptionMessage('Unsupported screener field [raw].');

        StructuredScreenerCriteria::fromFilters([
            ['field' => 'raw', 'operator' => 'raw', 'value' => 'market_cap > 1000'],
        ]);
    }

    public function test_provider_error_is_explicit_not_empty_data(): void
    {
        Cache::flush();
        config([
            'services.sectors.api_key' => 'test-sectors-key',
            'services.sectors.base_url' => 'https://api.example.test/v2',
        ]);

        Http::fake([
            'https://api.example.test/v2/companies*' => Http::response([], 429),
        ]);

        $user = User::factory()->create();
        $criteria = StructuredScreenerCriteria::fromFilters([
            ['field' => 'sector', 'operator' => 'eq', 'value' => 'Financials'],
        ]);

        try {
            app(StructuredCompanyScreener::class)->screen($user->id, $criteria, 'corr-screener-error');
            $this->fail('Provider errors should stay explicit.');
        } catch (SectorsApiException $exception) {
            $this->assertSame('provider_rate_limited', $exception->reason());
        }

        $this->assertDatabaseHas('market_data_credit_reservations', [
            'user_id' => $user->id,
            'endpoint' => 'companies',
            'estimated_credits' => 1,
            'correlation_id' => 'corr-screener-error',
        ]);
    }
}
