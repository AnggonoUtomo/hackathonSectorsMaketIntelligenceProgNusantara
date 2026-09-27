<?php

namespace Tests\Unit\Comparison;

use App\Modules\Comparison\Application\ComparisonSelectionBuilder;
use InvalidArgumentException;
use Tests\TestCase;

class ComparisonSelectionBuilderTest extends TestCase
{
    public function test_it_returns_empty_selection_without_symbols(): void
    {
        $payload = app(ComparisonSelectionBuilder::class)->build(null);

        $this->assertSame([], $payload['symbols']);
        $this->assertSame([], $payload['companies']);
        $this->assertSame([], $payload['metrics']);
        $this->assertSame('empty', $payload['meta']['state']);
        $this->assertSame(3, $payload['meta']['limit']);
        $this->assertSame('selection', $payload['meta']['source']);
    }

    public function test_it_builds_selection_without_fake_metrics_for_symbols(): void
    {
        $payload = app(ComparisonSelectionBuilder::class)->build('BBCA,TLKM');

        $this->assertSame(['BBCA', 'TLKM'], $payload['symbols']);
        $this->assertSame('selected', $payload['meta']['state']);
        $this->assertSame([], $payload['companies']);
        $this->assertSame([], $payload['metrics']);
    }

    public function test_it_rejects_more_than_three_symbols(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Comparison supports up to 3 symbols.');

        app(ComparisonSelectionBuilder::class)->build('BBCA,TLKM,ICBP,BBRI');
    }

    public function test_it_accepts_real_symbols_without_pending_fake_matrix(): void
    {
        $payload = app(ComparisonSelectionBuilder::class)->build('ADES,AADI');

        $this->assertSame(['ADES', 'AADI'], $payload['symbols']);
        $this->assertSame('selected', $payload['meta']['state']);
        $this->assertSame([], $payload['companies']);
        $this->assertSame([], $payload['metrics']);
    }

    public function test_it_rejects_invalid_symbol_format(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid comparison symbol [RAW-EXPRESSION].');

        app(ComparisonSelectionBuilder::class)->build('BBCA,raw-expression');
    }
}
