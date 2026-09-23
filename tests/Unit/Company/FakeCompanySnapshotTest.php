<?php

namespace Tests\Unit\Company;

use App\Modules\Company\Application\FakeCompanySnapshot;
use Tests\TestCase;

class FakeCompanySnapshotTest extends TestCase
{
    public function test_it_returns_fake_company_snapshot_by_symbol(): void
    {
        $snapshot = app(FakeCompanySnapshot::class)->find('bbca');

        $this->assertNotNull($snapshot);
        $this->assertSame('BBCA', $snapshot['symbol']);
        $this->assertSame('Bank Central Asia Tbk', $snapshot['name']);
        $this->assertSame('backend_fake', $snapshot['meta']['source']);
        $this->assertSame('Nilai Prioritas Riset', $snapshot['metrics'][0]['label']);
    }

    public function test_it_returns_pending_snapshot_for_unknown_valid_symbol(): void
    {
        $snapshot = app(FakeCompanySnapshot::class)->find('ADES.JK');

        $this->assertNotNull($snapshot);
        $this->assertSame('ADES', $snapshot['symbol']);
        $this->assertSame('detail_pending', $snapshot['meta']['source']);
    }

    public function test_it_returns_null_for_invalid_symbol(): void
    {
        $this->assertNull(app(FakeCompanySnapshot::class)->find('raw-expression'));
    }
}
