<?php

namespace Tests\Unit\Comparison;

use App\Modules\Comparison\Application\FakeComparisonBuilder;
use InvalidArgumentException;
use Tests\TestCase;

class FakeComparisonBuilderTest extends TestCase
{
    public function test_it_returns_empty_fake_comparison_without_symbols(): void
    {
        $payload = app(FakeComparisonBuilder::class)->build(null);

        $this->assertSame([], $payload['symbols']);
        $this->assertSame('empty', $payload['meta']['state']);
        $this->assertSame(3, $payload['meta']['limit']);
    }

    public function test_it_builds_fake_metric_matrix_for_symbols(): void
    {
        $payload = app(FakeComparisonBuilder::class)->build('BBCA,TLKM');

        $this->assertSame(['BBCA', 'TLKM'], $payload['symbols']);
        $this->assertSame('ready', $payload['meta']['state']);
        $this->assertSame('Nilai Prioritas Riset', $payload['metrics'][0]['label']);
        $this->assertSame('82,45', $payload['metrics'][0]['values']['BBCA']);
        $this->assertSame('78,20', $payload['metrics'][0]['values']['TLKM']);
    }

    public function test_it_rejects_more_than_three_symbols(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Comparison supports up to 3 symbols.');

        app(FakeComparisonBuilder::class)->build('BBCA,TLKM,ICBP,BBRI');
    }

    public function test_it_rejects_unknown_symbol(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown comparison symbol [ZZZZ].');

        app(FakeComparisonBuilder::class)->build('BBCA,ZZZZ');
    }
}
