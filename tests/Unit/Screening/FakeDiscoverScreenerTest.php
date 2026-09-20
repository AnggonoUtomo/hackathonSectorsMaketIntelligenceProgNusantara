<?php

namespace Tests\Unit\Screening;

use App\Modules\Screening\Application\FakeDiscoverScreener;
use Tests\TestCase;

class FakeDiscoverScreenerTest extends TestCase
{
    public function test_it_filters_fake_companies_by_sector_and_min_score(): void
    {
        $payload = app(FakeDiscoverScreener::class)->search([
            'sector' => 'financials',
            'min_score' => '80',
            'limit' => '10',
        ]);

        $this->assertSame('financials', $payload['filters']['sector']);
        $this->assertSame('80', $payload['filters']['minScore']);
        $this->assertSame('ready', $payload['meta']['state']);
        $this->assertCount(1, $payload['results']);
        $this->assertSame('BBCA', $payload['results'][0]['symbol']);
    }

    public function test_it_returns_empty_state_when_fake_keyword_has_no_match(): void
    {
        $payload = app(FakeDiscoverScreener::class)->search([
            'keyword' => 'ZZZZ',
        ]);

        $this->assertSame('ZZZZ', $payload['filters']['keyword']);
        $this->assertSame('empty', $payload['meta']['state']);
        $this->assertSame([], $payload['results']);
    }
}
