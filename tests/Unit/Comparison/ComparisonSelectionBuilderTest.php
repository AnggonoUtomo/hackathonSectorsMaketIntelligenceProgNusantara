<?php

namespace Tests\Unit\Comparison;

use App\Modules\Company\Application\Contracts\FactAttestation;
use App\Modules\Comparison\Application\ComparisonSelectionBuilder;
use App\Modules\MarketData\Application\Contracts\CompanyDirectory;
use InvalidArgumentException;
use Tests\TestCase;

class ComparisonSelectionBuilderTest extends TestCase
{
    private ComparisonSelectionBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = new ComparisonSelectionBuilder(new class implements CompanyDirectory
        {
            public function search(string $userId, string $query, int $page, int $perPage): array
            {
                return ['items' => [], 'total' => 0, 'page' => $page, 'perPage' => $perPage, 'fetchedAt' => now()->toIso8601String()];
            }

            public function profile(string $userId, string $symbol): array
            {
                return [
                    'symbol' => $symbol,
                    'name' => $symbol.' Company',
                    'logoUrl' => 'https://example.test/'.$symbol.'.webp',
                    'sector' => 'Financials',
                    'subSector' => 'Banks',
                    'industry' => 'Banking',
                    'price' => 6200,
                    'priceDate' => '2026-09-23',
                    'fetchedAt' => '2026-09-23T10:00:00+07:00',
                ];
            }
        }, app(FactAttestation::class));
    }

    public function test_it_returns_empty_selection_without_symbols(): void
    {
        $payload = $this->builder->build('user-1', null);

        $this->assertSame([], $payload['symbols']);
        $this->assertSame([], $payload['companies']);
        $this->assertSame([], $payload['metrics']);
        $this->assertSame('empty', $payload['meta']['state']);
        $this->assertSame(3, $payload['meta']['limit']);
        $this->assertSame('profile', $payload['meta']['source']);
        $this->assertSame(0, $payload['meta']['estimatedCredits']);
    }

    public function test_it_builds_profile_cards_without_fake_metrics_for_symbols(): void
    {
        $payload = $this->builder->build('user-1', 'BBCA,TLKM');

        $this->assertSame(['BBCA', 'TLKM'], $payload['symbols']);
        $this->assertSame('ready', $payload['meta']['state']);
        $this->assertSame('BBCA Company', $payload['companies'][0]['name']);
        $this->assertSame('Financials', $payload['companies'][0]['sector']);
        $this->assertSame('ready', $payload['companies'][0]['status']);
        $this->assertSame([], $payload['metrics']);
        $this->assertSame(2, $payload['meta']['estimatedCredits']);
    }

    public function test_it_rejects_more_than_three_symbols(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Comparison supports up to 3 symbols.');

        $this->builder->build('user-1', 'BBCA,TLKM,ICBP,BBRI');
    }

    public function test_it_accepts_real_symbols_without_fake_matrix(): void
    {
        $payload = $this->builder->build('user-1', 'ADES,AADI');

        $this->assertSame(['ADES', 'AADI'], $payload['symbols']);
        $this->assertSame('ready', $payload['meta']['state']);
        $this->assertSame('ADES', $payload['companies'][0]['symbol']);
        $this->assertSame([], $payload['metrics']);
    }

    public function test_it_rejects_invalid_symbol_format(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid comparison symbol [RAW-EXPRESSION].');

        $this->builder->build('user-1', 'BBCA,raw-expression');
    }
}
